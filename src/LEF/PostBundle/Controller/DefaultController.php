<?php
// src/LEF/PostBundle/Controller/DefaultController.php

namespace LEF\PostBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use LEF\PostBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogicException;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\PostBundle\Form\Type\EditPostType;
use LEF\PostBundle\Form\Type\ChildPostType;
use LEF\PostBundle\Form\Type\WallPostType;  
use LEF\PostBundle\Form\Type\PostType;  
use LEF\PostBundle\Event\PostEvent;
use LEF\CoreBundle\Event\PlatformEvents;
//use LEF\UserBundle\Entity\User;
use LEF\PostBundle\Form\Type\SearchPostType;
use LEF\CoreBundle\Component\Search\Object\SearchPost;

class DefaultController extends Controller {
    use ControllerTrait;
    public $pathPrefix = 'LEFPostBundle\\Default\\';

    public function indexAction($index, Request $request) {
        $limit = $this->container->getParameter('group_posts_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFPostBundle:Post');
        $offset = $index * $limit;
        $posts = $rep->findWithAll(array('likes' => 'DESC', 'dislikes' => 'ASC',
            'publishedAt' => 'DESC'
        ), $limit, $offset);
        $scrollable = count($posts) > 0 ? 'true' : 'false';
        return $this->doRender($request, 'index.html.twig', array(
            'scrollable' => $scrollable,
            'posts' => $posts,
            'total' => count($posts),
            'index' => $index
        ));        
    }

  
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFPostBundle:Post');
        $group = null;        
        $author = $this->get('authentication_context')->getUser();
        $post = new Post(['author' => $author]);
        if($request->request->has('wall') || $request->query->has('wall'))
            $form = $this->get('form.factory')->create(WallPostType::class, $post);
        else 
            $form = $this->get('form.factory')->create(PostType::class, $post);
        $form->handleRequest($request);            
        if($request->query->has('wall') || $form->has('wall'))
            $vars['wall'] = true;
        if($form->isSubmitted() && $form->isValid()) {     
            //throw new \RuntimeException('exce : ' . $request->request->has('wall') . ', ' . $request->query->has('wall'));
            $rep->persistAsFirstChild($post);
            $em->flush();
            $event = new PostEvent($post);
            $this->get('event_dispatcher')->dispatch(PlatformEvents::ADD_POST, $event);
            if(!$form->has('wall') && $request->isXmlHttpRequest())
                return $this->doRender($request, '', array(), array(
                    'alert' => true, 'alertStatus' => 'success', 
                    'alertText' =>  'flash.added.post'));
            $request->getSession()->getFlashBag()
                    ->add('info', 'flash.added.post');  
                
            $newPost = new Post(['author' => $author]);
            $form = $this->get('form.factory')->create(WallPostType::class, $newPost);
                
            return $this->render('LEFPostBundle\Default\add.html.twig', array(
                'form' => $form->createView(),
                'post' => $post,
                'alert' => true
            ));
        }
        $vars['post'] = $post;
        $vars['form'] = $form->createView();
        if($request->query->has('wall') || $form->has('wall'))
            return $this->render('LEFPostBundle\Default\add.html.twig', $vars);
        return $this->render('LEFPostBundle\Default\ajax\add.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addToWallAction(Request $request) {
        
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function editAction(Post $post, Request $request) {
        if(!$this->get('post_authorization')->isGranted('EDIT', $post))
            throw new AccessDeniedException('exception.accessdenied.lefpost.edit');
        
        $form = 
        $this->get('form.factory')
             ->create(EditPostType::class, $post); 
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {    
            $em = $this->get('doctrine.orm.entity_manager');
            $vars = [];
            $translator = $this->get('translator');
            if($this->getUser() != $post->getAuthor()) {
                $editData = [];
                if(!$post->isEdited()) {
                    $post->setEdited(true);
                    $editData['edited'] =  $translator->trans('edited.pp');
                }
                $post->setEditor($this->getUser());
                $editData['edited-by'] = $translator->trans('edited.by');
                $editData['alias'] = $post->getEditor()->getAlias();
                $editData['url'] = $this->generateUrl('lef_user_profile_show', array('id' => $this->getUser()->getId()));
                $vars['edit'] = $editData;
            }
            $em->flush();  
            $vars['content'] = $post->getContent();
            $vars['alertStatus'] = 'info';
            $vars['alertText'] = $translator->trans('flash.modified.post');
            
            return $this->doRender($request, 'view.html.twig', array('post' => $post), $vars);
        }
        
        return $this->doRender($request, 'edit.html.twig', array('form' => $form->createView(), 'post' => $post));        
    }
    
    /**
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function viewAction(Post $post, Request $request) {
        $authChecker = $this->get('post_authorization');
        if($authChecker->isBlocked($post->getAuthor()))
            throw new AccessDeniedException('exception.accessdenied.lefpost.view');
        $vars = [];
        if($authChecker->isGranted('COMMENT', $post))
            $vars['subresponse'] = $this->addCommentAction($post, $request)->getContent();
        $vars['post'] = $post;
        $notifSession = $this->get('notif_session');
        if($notifSession->has('post', $post->getId())) {
            $em->getRepository('LEFPostBundle:PostNotification')
            ->setViewed($this->getUser()->getId(), $post->getId());
            $notifSession->remove('post', $post->getId());
        }
        return $this->doRender($request, 'view.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function addCommentAction(Post $post, Request $request) {
        if(!$this->get('post_authorization')->isGranted('COMMENT', $post))
            throw new AccessDeniedException('exception.accessdenied.lefpost.add_comment');
         
        $author = $this->getUser();
        $comment = new Post(['author' => $author, 'lvl' => $post->getLvl() + 1]);
        
        $form = $this->get('form.factory')->create(ChildPostType::class, $comment);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $rep = $em->getRepository('LEFPostBundle:Post');
            $comment->setParent($post);
            $rep->persistAsLastChildOf($comment, $post);
            $em->flush();
            $request->getSession()->getFlashBag()
                    ->add('info', ('flash.added.') . ($comment->isRepost() ? 'repost' : 'comment'));
            $newcomment = new Post(['author' => $author]);
            $newcomment->setLvl($comment->getLvl());
            $form = $this->get('form.factory')->create(ChildPostType::class, $newcomment);
            
            return $this->render('LEFPostBundle\Default\addComment.html.twig', array(
                'comment' => $comment,
                'form' => $form->createView(),
                'post' => $post,
                'alert' => true
            ));
        }
        
        return $this->render('LEFPostBundle\Default\addComment.html.twig', array(
            'form' => $form->createView(),
            'post' => $post
        ));
    }
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function deleteAction(Post $post, Request $request) {
        $authChecker = $this->get('post_authorization');
        if($authChecker->isGranted('DELETE', $post) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefpost.delete');
        
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $rep = $em->getRepository('LEFPostBundle:Post');
            $author = $post->getAuthor();
            
            $children = $post->getChildren();
            $comments = 0;
            foreach($children as $child) 
                if(!$child->isRepost()) {
                    //$post->removeChild($child);
                    //$rep->removeFromTree($child);
                    $em->remove($child);
                    $comments += $child->getNbComments();
                } else {
                    $child->setRepost(false);
                }
                
            $em->flush();
            $id = 'post-' . $post->getId();
            
            $parent = $post->getParent();
            if($parent != null && !$post->isBanned() && $post->getLvl() === 1) {
                $comments = $parent->getNbComments() - $comments;
                $parent->setNbComments($comments);
            }
            $rep->removeFromTree($post);
            $em->remove($post);
            //$em->clear();
            
            $em->flush();
            
            if($request->query->has('_target_path')) {
                $request->getSession()->getFlashBag()->add('warning', 'flash.deleted.post');
                return $this->redirectToTargetPath($request);
            }
            return $this->doRender($request, 'lef_group_post_view', array('id' => $post->getId()),
                array('id' => $id, 'alert' => true,
                    'alertStatus' => 'warning', 'alertText' => 'flash.deleted.post')
            );
        }
        return $this->doRender($request, 'delete.html.twig', array(
            'form' => $form->createView(),
            'post' => $post
        ));                    
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithParent"})
     */
    public function banAction(Post $post, Request $request) {
        $authChecker = $this->get('post_authorization');
        $parent = $post->getParent();
        if(!$post->isRepost() || $post->isBanned())
            throw new LogicException('exception.logic.lefpost.ban_post');
        if($authChecker->isGranted('BAN', $post) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefpost.ban_post');
                
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $post->setBanned(true);
            $parent->setNbComments(($parent->getNbComments() - $post->getNbComments() - 1));
            $id = 'post-' . $post->getId();  
            $em->flush();
            return $this->doRender($request, 'lef_post_view', array('id' => $parent->getId()),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 'alertText' => 'flash.banned.repost')
            );
         }
         return $this->doRender($request, 'ban.html.twig', array(
            'form' => $form->createView(),
            'post' => $post
         ));
    }
    
    public function searchAction($index, Request $request) {
        $search = new SearchPost();
        if($request->query->has('input') || $request->request->has('input'))
            $search->setInput($request->query->has('input')
                ? $request->query->get('input') : $request->request->get('input'));
            
        $form = $this->get('form.factory')->create(SearchPostType::class, $search);
        $form->handleRequest($request);
        $isAjax = $request->query->has('_token') && $this->isCsrfTokenValid('search_item', $request->query->get('_token'));
        $searcher = $this->get('lef_core.component.search.searcher');
        if(($form->isSubmitted() && $form->isValid()) || $isAjax) {
            $search->processInput();
            //throw new \RuntimeException('voila : ' . print_r($search, true));
            $vars = $searcher->searchPosts($search, $index, $request);
            $vars['form'] = $form->createView();
                
            return $this->doRender($request, 'search.html.twig', $vars);
        }
        else {
            $search->processInput();
            $vars = $searcher->searchPosts($search, $index, $request);
            $vars['form'] = $form->createView();
            return $this->doRender($request, 'search.html.twig', $vars);
        }
        //throw new \RuntimeException('voila : ' . print_r($search, true));
            
    }
}