<?php
// src/LEF/GroupBundle/Controller/PostController.php

namespace LEF\GroupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use LEF\GroupBundle\Entity\GroupPost;
use LEF\GroupBundle\Entity\Group;
use LEF\GroupBundle\Entity\MemberPrivilege; 
use LEF\GroupBundle\Form\Type\RootGroupPostType;
use LEF\GroupBundle\Form\Type\ChildGroupPostType;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogicException;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\GroupBundle\Form\Type\EditGroupPostType;
use LEF\GroupBundle\Form\Type\WallGroupPostType;  
use LEF\GroupBundle\Event\GroupPostEvent;
use LEF\CoreBundle\Event\PlatformEvents;


class PostController extends Controller {
    use ControllerTrait;
    public $pathPrefix = 'LEFGroupBundle\\Post\\';

    public function indexAction($index, Request $request) {
        $limit = $this->container->getParameter('group_posts_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFGroupBundle:GroupPost');
        $offset = $index * $limit;
        $groupPosts = $rep->findWithAll(array('likes' => 'DESC', 'dislikes' => 'ASC',
            'publishedAt' => 'DESC'
        ), $limit, $offset);
        $scrollable = count($groupPosts) > 0 ? 'true' : 'false';
        return $this->doRender($request, 'index.html.twig', array(
            'scrollable' => $scrollable,
            'groupPosts' => $groupPosts,
            'total' => count($groupPosts),
            'index' => $index
        ));        
    }
    
    /**
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function groupPostsAction(Group $group, Request $request) {
        if($this->get('group_block_session')->isBlocker($group->getId()))
            return $this->render('LEFGroupBundle\Default\blocked_view.html.twig', array('group' => $group));
            //throw new AccessDeniedException('exception.accessdenied.lefgroup.blocked');
        $limit = $this->container->getParameter('group_posts_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFGroupBundle:GroupPost');
        $index = $request->query->get('index') ?: 0;
        $offset = $index * $limit;
        $groupPosts = $rep->myfindByGroup($group->getId(), array('publishedAt' => 'DESC'), $limit, $offset, true);
        
        $scrollable = count($groupPosts) ? 'true' : 'false';
        $authChecker = $this->get('group_authorization');
        $authContext = $this->get('authentication_context');
        $vars = ['scrollable' => $scrollable,
            'groupPosts' => $groupPosts,
            'total' => count($groupPosts),
            'index' => $index,
            'group' => $group
        ];
        if($index == 0 && $authContext->isAuthenticated() && $authChecker->isGranted(PrivilegeBitmasks::POST, $group))
            $vars['subresponse'] = $this->addAction($group, $request)->getContent();
        return $this->doRender($request, 'groupPosts.html.twig', $vars);
    }
    
    /**
     * @ParamConverter("group", options={"repository_method": "findOneWithAll"})
     */
    public function restrictedPostsAction(Group $group, Request $request) {
        if(!$this->get('group_authorization')->isGranted('VIEW', $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.unpublished_articles');
        $limit = $this->container->getParameter('group_posts_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFGroupBundle:GroupPost');
        $index = $request->query->get('index') ?: 0;
        $offset = $index * $limit;
        $groupPosts = $rep->myfindByGroup($group->getId(), array('publishedAt' => 'DESC'), 
            $limit, $offset, $public = false);
        
        $scrollable = count($groupPosts) ? 'true' : 'false';
        $authChecker = $this->get('group_authorization');
        $authContext = $this->get('authentication_context');
        $request->query->add(['restricted' => true]);
        $vars = array(
            'scrollable' => $scrollable,
            'groupPosts' => $groupPosts,
            'total' => count($groupPosts),
            'index' => $index,
            'group' => $group
        );
        if($index == 0 && $authContext->isAuthenticated() && $authChecker->isGranted(PrivilegeBitmasks::POST, $group))
            $vars['subresponse'] = $this->addAction($group, $request)->getContent();
        return $this->doRender($request, 'restrictedPosts.html.twig', $vars);
    }
    
    public function viewByCategoryAction($id, Request $request) {
        $limit = $this->container->getParameter('group_posts_limit');
        $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFGroupBundle:GroupPost');
        $index = $request->query->get('index') ?: 0;
        $offset = $index * $limit;
        $groupPosts = $rep->myFindByCategory($id, array('likes' => 'DESC', 'dislikes' => 'ASC',
            'publishedAt' => 'DESC'
        ), $limit, $offset);
        $scrollable = count($groupPosts) ? 'true' : 'false';
        return $this->doRender($request, 'viewByCategory.html.twig', array(
            'scrollable' => $scrollable,
            'groupPosts' => $groupPosts,
            'total' => count($groupPosts),
            'index' => $index,
            'id' => $id
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function addAction($id, Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:GroupPost');
        $group = null;
        if(!empty($id)) {
            $group = $em->getRepository('LEFGroupBundle:Group')->findOneBy(array('id' => $id));
            if(empty($group))
                throw new NotFoundHttpException('exception.notfound.lefgroup.add_post');
        }
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::POST, $group))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.add_post');
        $author = $this->get('authentication_context')->getUser();
        if(empty($group)) {
            $groupPost = new GroupPost(['author' => $author]);
            $form = $this->get('form.factory')->create(RootGroupPostType::class, $groupPost);
        } else {
            $groupPost = new GroupPost(['group' => $group, 'author' => $author]);
            if($request->query->has('restricted'))
                $groupPost->setPublicPost(false);
            $form = $this->get('form.factory')->create(WallGroupPostType::class, $groupPost);
        }
        $form->handleRequest($request);                
        if($form->isSubmitted() && $form->isValid()) {            
            $rep->persistAsFirstChild($groupPost);
            if($form->has('is_public') && $form->get('is_public')->getData() == 'false')
                $groupPost->setPublic(false);
            $em->flush();
            $event = new GroupPostEvent($groupPost);
            $this->get('event_dispatcher')->dispatch(PlatformEvents::ADD_GROUP_POST, $event);
            $request->getSession()->getFlashBag()
                    ->add('info', 'flash.added.'. ($groupPost->isPublic() ? '': 'restricted_') . 'post');    
            if(empty($id))
                return $this->redirectToRoute('lef_group_post_view', array(
                    'id' => $groupPost->getId()
                ));
                
            $newPost = new GroupPost(['group' => $groupPost->getGroup(), 'author' => $author, 
                'publicPost' => $groupPost->isPublic()]);
            $form = $this->get('form.factory')->create(WallGroupPostType::class, $newPost);
                
            return $this->render('LEFGroupBundle\Post\ajax\add.html.twig', array(
                'form' => $form->createView(),
                'post' => $groupPost,
                'alert' => true
            ));
        }
        
        if(empty($id))
            return $this->render('LEFGroupBundle\Post\add.html.twig', array('form' => $form->createView()));
        
        return $this->render('LEFGroupBundle\Post\ajax\add.html.twig', array('form' => $form->createView(),
            'post' => $groupPost
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function editAction(GroupPost $post, Request $request) {
        if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::EDIT, $post->getGroup(), $post->getAuthor()))
            throw new NotFoundHttpException('exception.notfound.lefgroup.edit_post');
        
        $form = 
        $this->get('form.factory')
             ->create(EditGroupPostType::class, $post); 
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
    public function viewAction(GroupPost $post, Request $request) {
        $authChecker = $this->get('group_authorization');
        $authContext = $this->get('authentication_context');
        $em = $this->get('doctrine.orm.entity_manager');
        $vars = [];
        if($authContext->isAuthenticated() && ($post->isPublic() || $authChecker->isGranted(PrivilegeBitmasks::COMMENT, $post->getGroup())))
            $vars['subresponse'] = $this->addCommentAction($post, $request)->getContent();
        $vars['post'] = $post;
        $notifSession = $this->get('notif_session');
        if($notifSession->has('group_post', $post->getId())) {
            $em->getRepository('LEFGroupBundle:GroupPostNotification')
            ->setViewed($this->getUser()->getId(), $post->getId());
            $notifSession->remove('group_post', $post->getId());
        }
       
        return $this->doRender($request, 'view.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithGroup"})
     */
    public function addCommentAction(GroupPost $post, Request $request) {
        if(!$post->isPublic() && !$this->get('group_authorization')->isGranted(PrivilegeBitmasks::COMMENT, $post->getGroup()))
            throw new AccessDeniedException('exception.accessdenied.lefgroup.add_comment');
         
        $author = $this->get('authentication_context')->getUser();
        $comment = new GroupPost(['group' => $post->getGroup(), 'author' => $author, 
                                  'publicPost' => $post->isPublic(), 'lvl' => $post->getLvl() + 1]);
        
        $form = $this->get('form.factory')->create(ChildGroupPostType::class, $comment);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $rep = $em->getRepository('LEFGroupBundle:GroupPost');
            $comment->setParent($post);     
           
            $rep->persistAsLastChildOf($comment, $post);
            $em->flush();
            $request->getSession()->getFlashBag()
                    ->add('info', ('flash.added.') . ($comment->isRepost() ? 'repost' : 'comment'));
            $newcomment = new GroupPost(['group' => $post->getGroup(), 'author' => $author, 'publicPost' => $post->isPublic()]);
            $newcomment->setLvl($comment->getLvl());
            $form = $this->get('form.factory')->create(ChildGroupPostType::class, $newcomment);
            
            return $this->render('LEFGroupBundle\Post\addComment.html.twig', array(
                'comment' => $comment,
                'form' => $form->createView(),
                'groupPost' => $post,
                'alert' => true
            ));
        }
        
        return $this->render('LEFGroupBundle\Post\addComment.html.twig', array(
            'form' => $form->createView(),
            'groupPost' => $post
        ));
    }
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function deleteAction(GroupPost $post, Request $request) {
        $authChecker = $this->get('group_authorization');
        if($authChecker->isGranted(PrivilegeBitmasks::DELETE, $post->getGroup(), $post->getAuthor()) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.delete_post');
        
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $rep = $em->getRepository('LEFGroupBundle:GroupPost');
            $group = $post->getGroup();
            $children = $post->getChildren();
            $comments = 0;
            foreach($children as $child) 
                if($child->getGroup() == $group) {
                    //$post->removeChild($child);
                    //$rep->removeFromTree($child);
                    $em->remove($child);
                    $comments += $child->getNbComments();
                } else {
                    $child->setRepost(false);
                }
            //$em->persist($group);
            $em->flush();
            $id = 'group-post-' . $post->getId();
            $parent = $post->getParent();
            if($parent != null && !$post->isBanned() && $post->getLvl() === 1) {
                $comments = $parent->getNbComments() - $comments;
                $parent->setNbComments($comments);
            }
            $rep->removeFromTree($post);
            $id = 'group-post-' . $post->getId();
            //$rep->clear();
            $em->remove($post);
            
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
     * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
     */
    public function banAction(GroupPost $post, Request $request) {
        $authChecker = $this->get('group_authorization');
        $parent = $post->getParent();
        $group = empty($parent) ?: $parent->getGroup();
        if(!$post->isRepost() || $post->isBanned())
            throw new LogicException('exception.logic.lefgroup.ban_post');
        if(empty($group) || $authChecker->isGranted(PrivilegeBitmasks::DELETE, $group) !== true)
            throw new AccessDeniedException('exception.accessdenied.lefgroup.ban_post');
            
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $post->setBanned(true);
            $parent->setNbComments(($parent->getNbComments() - $post->getNbComments() - 1));
            $id = 'group-post-' . $post->getId();
                      
            $em->flush();
            return $this->doRender($request, 'lef_group_post_view', array('id' => $parent->getId()), 
                array('id' => $id, 'alert' => true, 
                    'alertStatus' => 'warning', 'alertText' => 'lefgroup.banned.repost')
            );
        }
        return $this->doRender($request, 'ban.html.twig', array(
            'form' => $form->createView(),
            'post' => $post
        ));            
    }
}