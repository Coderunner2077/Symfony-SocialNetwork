<?php

namespace LEF\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use LEF\ArticleBundle\Entity\Article;
use LEF\ArticleBundle\Entity\Comment;  
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\User\UserInterface;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use LEF\ArticleBundle\Form\Type\ArticleType;
use LEF\ArticleBundle\Entity\ArticleLike;
use LEF\ArticleBundle\Entity\ArticleDislike;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\ArticleBundle\Event\ArticleEvent;
use LEF\ArticleBundle\Form\Type\CommentType;
use LEF\CoreBundle\Event\PlatformEvents;
use LEF\CoreBundle\Component\Search\Object\SearchArticle;
use LEF\ArticleBundle\Form\Type\SearchArticleType;

class DefaultController extends Controller {	 
    public $pathPrefix = 'LEFArticleBundle\\Default\\';
    use ControllerTrait;
    
	public function indexAction($index, Request $request) {
		$nombreNews = $this->container->getParameter('articles_limit');
		$rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
		$offset = $index * $nombreNews;
		$listeNews = $rep->findWithAll(array(
		    'likes' => 'DESC',
		    'dislikes' => 'ASC',
		    'publishedAt' => 'DESC'), $nombreNews, $offset);
		
		$scrollable = count($listeNews) ? 'true' : 'false';
		return $this->doRender($request, 'index.html.twig', array(
		    'scrollable' => $scrollable,
		    'articles' => $listeNews,
		    'total' => count($listeNews),
		    'index' => $index
		)); 
	}
	
	public function viewByCategoryAction($id, Request $request) {
	    $nombreNews = $this->container->getParameter('articles_limit');
	    $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
	    $index = $request->query->get('index') ?: 0;
	    $offset = $index * $nombreNews;
	    $listeNews = $rep->getByCategory($id, array(
	        'likes' => 'DESC',
	        'dislikes' => 'ASC',
	        'publishedAt' => 'DESC'), $nombreNews, $offset);
	   
	    $scrollable = count($listeNews) ? 'true' : 'false';
	    return $this->doRender($request, 'viewByCategory.html.twig', array(
	        'scrollable' => $scrollable,
	        'articles' => $listeNews,
	        'total' => count($listeNews),
	        'index' => $index,
	        'id' => $id
	    ));
	}
	
	public function viewByGroupCategoryAction($id, Request $request) {
	    $nombreNews = $this->container->getParameter('articles_limit');
	    $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
	    $index = $request->query->get('index') ?: 0;
	    $offset = $index * $nombreNews;
	    $listeNews = $rep->getByGroupCategory($id, array(
	        'likes' => 'DESC',
	        'dislikes' => 'ASC',
	        'publishedAt' => 'DESC'), $nombreNews, $offset);
	  
	    $scrollable =count($listeNews) ? 'true' : 'false';
	    
	    return $this->doRender($request, 'viewByGroupCategory.html.twig', array(
	        'scrollable' => $scrollable,
	        'articles' => $listeNews,
	        'total' => count($listeNews),
	        'index' => $index,
	        'id' => $id
	    ));
	}
	
	/**
	 * @ParamConverter("article", options={"repository_method": "findOneWithAll"})
	 */
	public function viewAction(Article $article, Request $request) {
	    if($this->isViewable($article) !== true) {
	        //throw new AccessDeniedException('exception.accessdenied.lefarticle.view');
	        $content = $article->getContent();
	        $content = substr($content, 0, (strlen($article->getContent()) * $article->getDisplay() / 100));
	        $content = preg_replace('#\s[a-zA-Z1-9._-]*$#', '...', $content);
	        $article->setContent($content);
	    } 
	    $authContext = $this->get('authentication_context');
	    $em = $this->get('doctrine.orm.entity_manager');
	    $vars['privilege'] = $em
	                      ->getRepository('LEFGroupBundle:MemberPrivilege')
	                      ->findOneBy(array('member' => $article->getAuthor(), 'group' => $article->getGroup()));
	    
	    $vars['article'] = $article;
	    $notifSession = $this->get('notif_session');
	    if($notifSession->has('article', $article->getId())) {
	        $em->getRepository('LEFArticleBundle:ArticleNotification')
	        ->setViewed($this->getUser()->getId(), $article->getId());
	        $notifSession->remove('article', $article->getId());
	    }
	    $authChecker = $this->get('group_authorization');
	    if($authContext->isAuthenticated() && !$this->get('group_block_session')->isBlocker($article->getGroup()->getId())
	        && ($article->getAllowComments() || $authChecker->isGranted(PrivilegeBitmasks::COMMENT, $article->getGroup())))
	        $vars['subresponse'] = $this->addPostAction($article, $request)->getContent();
		return $this->doRender($request, 'view.html.twig', $vars);		
	}
	
	protected function isViewable(Article $article) {
	    if($this->get('group_block_session')->isBlocker($article->getGroup()->getId()))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.view');
	    if($article->getPublished() === true && $article->isViewable())
	        return true;
	    
	    $groupSecurity = $this->get('group_authorization');
	    if($article->getPublished() === false)
	        $result = $groupSecurity->isGranted(PrivilegeBitmasks::EDIT, $article->getGroup(), $article->getAuthor());
	    else {
	        if(!$this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
	            return false;
	        $result = $groupSecurity->isGranted(PrivilegeBitmasks::VIEW, $article->getgroup(), $article->getAuthor());
	    }
	    
	    return $result;	    
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 */
	public function addAction(Request $request) {
	    $user = $this->get('security.token_storage')->getToken()->getUser();
	    if(true !== $this->get('group_authorization')->isGranted(PrivilegeBitmasks::CREATE))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.add');
	    
	    $article = new Article();
	   	$form = $this->get('form.factory')->create(ArticleType::class, $article);
	   	
	    if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	        $article->setAuthor($user);
	        $article->setGroup($form->get('group')->getData()->getGroup()); 
	        $em = $this->getDoctrine()->getManager();
	        $em->persist($article);
	        if(!empty($article->getImage()))
	            throw new \RuntimeException('voila : ' . $article->getImage()->getFile()->isFile());
	        $em->flush();
	        if($article->isPublished()) {
	            $event = new ArticleEvent($article);
	            $this->get('event_dispatcher')->dispatch(PlatformEvents::ADD_ARTICLE, $event);
	            $request->getSession()->getFlashBag()->add('success', 'flash.published.article');
	        }
	        else 
	            $request->getSession()->getFlashBag()->add('info', 'flash.added.article');
	       
	        
	        return $this->redirectToRoute('lef_article_view', array('id' => $article->getId()));
	    }
	    
	    return $this->render('LEFArticleBundle\Default\add.html.twig', array(
	        'form' => $form->createView()
	    ));
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("article", options={"repository_method": "findOneWithAll"})
	 */
	public function editAction(Article $article, Request $request) {
	    if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::EDIT, $article->getGroup(), $article->getAuthor()))
	        throw new NotFoundHttpException('exception.notfound.lefarticle.edit');
	        
	        $form =
	    $this->get('form.factory')->create(ArticleType::class, $article);
	    $content = $article->getContent();
	    $form->handleRequest($request);
	    if($form->isSubmitted() && $form->isValid() && $content != $article->getContent()) {
	        $em = $this->get('doctrine.orm.entity_manager');
	        $vars = [];
	        $translator = $this->get('translator');
	        if($this->getUser() != $article->getAuthor()) {
	            $article->setEditor($this->getUser());
	        }
	        $publishedAt = $article->getPublishedAt();
	        $em->flush();
	        if(!empty($article->getUpdatedAt()))
	            $request->getSession()->getFlashBag()->add('info', 'flash.modified.article');
	        elseif($publishedAt != $article->getPublishedAt()) {
	            $event = new ArticleEvent($article);
	            $this->get('event_dispatcher')->dispatch(PlatformEvents::PUBLISH_ARTICLE, $event);
	            $request->getSession()->getFlashBag()->add('success', 'flash.published.article');
	        }
	         
	        return $this->redirectToRoute('lef_article_view', array('id' => $article->getId()));
	    }
	        
	    return $this->doRender($request, 'edit.html.twig', array('form' => $form->createView(), 'article' => $article));
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("article", options={"repository_method": "findOneWithAll"})
	 */
	public function deleteAction(Article $article, Request $request) {
	    $authChecker = $this->get('group_authorization');
	    if(($authChecker->isGranted(PrivilegeBitmasks::DELETE, $article->getGroup(), $article->getAuthor())) !== true)
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.delete_article');
	        
	        $form = $this->get('form.factory')->create();
	        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	            $em = $this->get('doctrine.orm.entity_manager');
	            $id = 'article-' . $article->getId();
	                    
	            $em->remove($article);
	            $em->flush();
	                    
	            if($request->query->has('_target_path')) {
	                $request->getSession()->getFlashBag()->add('warning', 'flash.deleted.article');
	                return $this->redirectToTargetPath($request);
	            }
	            return $this->doRender($request, 'lef_article_view', array('id' => $article->getId()),
	                array('id' => $id, 'alert' => true,
	                      'alertStatus' => 'warning', 'alertText' => 'flash.deleted.article'
	                )
	            );
	        }
	        return $this->doRender($request, 'delete.html.twig', array(
	            'form' => $form->createView(),
	            'article' => $article
	        ));
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("article", options={"repository_method": "findOneWithAll"})
	 */
	public function addPostAction(Article $article, Request $request) {
	    if(!$article->getAllowComments() && !$this->get('group_authorization')->isGranted(PrivilegeBitmasks::COMMENT, $article->getGroup()))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.add_post');
	    if($this->get('group_block_session')->isBlocker($article->getGroup()->getId()))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.add_post_blocked');   
	    $author = $this->get('authentication_context')->getUser();
	    $comment = new Comment(['article' => $article, 'author' => $author]);
	        
	    $form = $this->get('form.factory')->create(CommentType::class, $comment);
	    $form->handleRequest($request);
	        
	    if($form->isSubmitted() && $form->isValid()) {
	        $em = $this->get('doctrine.orm.entity_manager');
	        $rep = $em->getRepository('LEFArticleBundle:Comment');
	        $rep->persistAsFirstChild($comment);
	        $em->flush();
	        $request->getSession()->getFlashBag()
	                ->add('info', ('flash.added.comment'));
	        $newcomment = new Comment(['ticle' => $article, 'author' => $author]);
	        $form = $this->get('form.factory')->create(CommentType::class, $newcomment);
	                
	        return $this->render('LEFArticleBundle\Default\addPost.html.twig', array(
	             'comment' => $comment,
	             'form' => $form->createView(),
	             'article' => $article,
	             'alert' => true
	        ));
	    }
	        
	    return $this->render('LEFArticleBundle\Default\addPost.html.twig', array(
	        'form' => $form->createView(),
	        'article' => $article
	    ));
	}
	
	/**
	 * @ParamConverter("article", options={"repository_method": "findOneWithComments"})
	 */
	public function viewPostsAction(Article $article, Request $request) {
	    $authContext = $this->get('authentication_context');
	    $authChecker = $this->get('group_authorization');
	    if($authContext->isAuthenticated() && ($article->getAllowComments() || $authChecker->isGranted(PrivilegeBitmasks::COMMENT, $article->getGroup())))
	        $vars['subresponse'] = $this->addPostAction($article, $request)->getContent();
	    $vars['posts'] = $article->getComments();
	    $vars['article'] = $article;
	    return $this->doRender($request, 'viewPosts.html.twig', $vars);
	}
	
	/**
	 * @ParamConverter("comment", options={"repository_method": "findOneWithAll"})
	 */
	public function viewCommentsAction(Comment $post, Request $request) {
	    $authChecker = $this->get('group_authorization');
	    $authContext = $this->get('authentication_context');
	    $em = $this->get('doctrine.orm.entity_manager');
	    $article = $post->getArticle();
	    $vars['group'] = $article->getGroup();
	    if($authContext->isAuthenticated() && !$this->get('group_block_session')->isBlocker($article->getGroup()->getId())
	        && ($article->getAllowComments() || $authChecker->isGranted(PrivilegeBitmasks::COMMENT, $vars['group'])))
	        $vars['subresponse'] = $this->addCommentAction($post, $request)->getContent();
	    $vars['post'] = $post;
	    return $this->doRender($request, 'viewComments.html.twig', $vars);
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
	 */
	public function editPostAction(Comment $post, Request $request) {
	    $article = $post->getArticle();
	    if(!$this->get('group_authorization')->isGranted(PrivilegeBitmasks::EDIT, $article->getGroup(), $post->getAuthor()))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.edit_post');
	        
	    $form = $this->get('form.factory')->create(CommentType::class, $post);
	    $form->handleRequest($request);
	    if($form->isSubmitted() && $form->isValid()) {
	        $em = $this->get('doctrine.orm.entity_manager');
	        $vars = [];
	        $translator = $this->get('translator');
	        if($this->getUser() != $post->getAuthor()) {
	            $editData = [];
	            if(!$post->isEdited())
	                $editData['edited'] =  $translator->trans('edited.pp');
	            
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
	            
	        return $this->doRender($request, 'lef_article_view', array('id' => $article->getId()), $vars);
	    }
	        
	    return $this->doRender($request, 'editPost.html.twig', array('form' => $form->createView(), 'post' => $post));
	}
	
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
	 */
	public function addCommentAction(Comment $post, Request $request) {
	    $article = $post->getArticle();
	    $group = $article->getGroup();
	    if(!$article->getAllowComments() && !$this->get('group_authorization')->isGranted(PrivilegeBitmasks::COMMENT, $group))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.add_comment');
	    if($this->get('group_block_session')->isBlocker($article->getGroup()->getId()))
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.add_post_blocked');  
	    $author = $this->get('authentication_context')->getUser();
	    $comment = new Comment(['article' => $article, 'author' => $author]);
	        
	    $form = $this->get('form.factory')->create(CommentType::class, $comment);
	    $form->handleRequest($request);
	        
	    if($form->isSubmitted() && $form->isValid()) {
	        $em = $this->get('doctrine.orm.entity_manager');
	        $rep = $em->getRepository('LEFArticleBundle:Comment');
	        $rep->persistAsLastChildOf($comment, $post);
	        $em->flush();
	        $request->getSession()->getFlashBag()
	        ->add('info', ('flash.added.comment'));
	        $newcomment = new Comment(['article' => $article, 'author' => $author]);
	        $form = $this->get('form.factory')->create(CommentType::class, $newcomment);
	        
	        return $this->render('LEFArticleBundle\Default\addComment.html.twig', array(
	            'comment' => $comment,
	            'form' => $form->createView(),
	            'post' => $post,
	            'group' => $group,
	            'alert' => true
	        ));
	       
	    }
	        
	    return $this->render('LEFArticleBundle\Default\addComment.html.twig', array(
	        'form' => $form->createView(),
	        'post' => $post
	    ));
	}
	/**
	 * @Security("has_role('ROLE_USER')")
	 * @ParamConverter("post", options={"repository_method": "findOneWithAll"})
	 */
	public function deletePostAction(Comment $post, Request $request) {
	    $authChecker = $this->get('group_authorization');
	    $article = $post->getArticle();
	    $group = $article->getGroup();
	    if($authChecker->isGranted(PrivilegeBitmasks::DELETE, $group, $post->getAuthor()) !== true)
	        throw new AccessDeniedException('exception.accessdenied.lefarticle.delete_post');
	        
	    $form = $this->get('form.factory')->create();
	    if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	        $em = $this->get('doctrine.orm.entity_manager');
	        $rep = $em->getRepository('LEFArticleBundle:Comment');
	        $id = 'comment-' . $post->getId();
	        $article->setNbComments($article->getNbComments() - $post->getNbComments() - 1);
	       
	        if($post->getLvl() > 0)
	            $post->getParent()->decrementComments();
	        $em->remove($post);            
	        $em->flush();
	        if($request->query->has('_target_path')) {
	            $request->getSession()->getFlashBag()->add('warning', 'flash.deleted.post');
	            return $this->redirectToTargetPath($request);
	        }
	        return $this->doRender($request, 'lef_article_view', array('id' => $article->getId()),
	            array('id' => $id, 'alert' => true,
	                  'alertStatus' => 'warning', 'alertText' => 'flash.deleted.post')
	        );
	    }
	    return $this->doRender($request, 'deletePost.html.twig', array(
	        'form' => $form->createView(),
	        'post' => $post
	    ));
	} 
	
	public function searchAction($index, Request $request) {
	    $search = new SearchArticle();
	    if($request->query->has('input') || $request->request->has('input'))
	        $search->setInput($request->query->has('input') 
	            ? $request->query->get('input') : $request->request->get('input'));
	    
	    $form = $this->get('form.factory')->create(SearchArticleType::class, $search);
	    $form->handleRequest($request);
	    $isAjax = $request->query->has('_token') && $this->isCsrfTokenValid('search_item', $request->query->get('_token'));
	    $searcher = $this->get('lef_core.component.search.searcher');
	    if(($form->isSubmitted() && $form->isValid()) || $isAjax) {
	        $search->processInput();
	        //throw new \RuntimeException('voila : ' . print_r($search, true));
	        $vars = $searcher->searchArticles($search, $index, $request);
	        $vars['form'] = $form->createView();
	        
	        return $this->doRender($request, 'search.html.twig', $vars);
	    } 
	    else {
	        $vars = $searcher->searchArticles($search, $index, $request);
	        $vars['form'] = $form->createView();
	        return $this->doRender($request, 'search.html.twig', $vars);
	    }
	    //throw new \RuntimeException('voila : ' . print_r($search, true));
	}
	
	/*
	public function searchAction($index, Request $request) {
	    if(!$request->isMethod('POST') || (!$request->request->has('search') && !$request->query->has('search'))) 
	        throw new LogicException('exception.logic.search');
	    $ids = $request->query->has('ids') ? unserialize($request->query->get('ids')) : array();
	    $expr = $request->request->has('search') ? $request->request->get('search') : $request->query->get('search');
	    $userInput = $expr;
	    if(preg_match("#[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\s\"';:_.,!?-]{4,100}#i", $expr)) {
	        //throw new LogicException('exception.logic.search');
	       $quotes = [];
	      
	       if(preg_match_all("#\"([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ][a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\s';:_.,!?-]{2,100}[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ])\"#i", 
	           $expr, $quotes))
	           $expr = preg_replace("#\"[a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\s'_.,;:!?-]{1,100}\"#i", '', $expr);
	       $words = [];
	       preg_match_all("#\b([a-z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]{1,100})\b[\s':;_.,!?-]{0,10}#i", $expr, $words);
	       $limit = $this->container->getParameter('articles_limit');
	       $offset = $index * $limit;
	       $rep = $this->get('doctrine.orm.entity_manager')->getRepository('LEFArticleBundle:Article');
	       $indexBis = $request->query->has('index_bis') ? $request->query->get('index_bis') : 0;
	       $offsetBis = $indexBis * $limit;
	       if(!empty($quotes)) 
	           $quotes = $quotes[1];
	       
	       if(!empty($words[1])) {
	           $words = $words[1];
	           //$quotes[] = implode(' ', $words);
	           $quotes = array_merge($quotes, $words);
	       }
	       //throw new \RuntimeException('quotes : ' . print_r($quotes, true) . ', words : ' . print_r($words, true));
	       $hasIndexBis = null;
	       if(!empty($quotes)) {
	           if($request->query->has('index_bis')) {
	               $totalBis = $rep->countSearchResult($quotes, $ids, true); $hasIndexBis = true;
	               $articles = $rep->searchArticles($quotes, array('publishedAt' => 'DESC'), $limit, $offsetBis, $ids, true);
	           } else {
	               $total = $rep->countSearchResult($quotes);
	               $articles = $rep->searchArticles($quotes, array('publishedAt' => 'DESC'), $limit, $offset);
	               $artIds = empty($articles) ? array() :
	                               array_map(function($article) {
	                                   return $article->getId();
	                               } , $articles);
	               $ids = array_merge($ids, $artIds);
	           }
	           
	           if(empty($articles) && $hasIndexBis == null && count($quotes) > 1) {
	               $totalBis = $rep->countSearchResult($quotes, $ids, true); $hasIndexBis = true;
	               $articles = $rep->searchArticles($quotes, array('publishedAt' => 'DESC'), $limit, $offsetBis, $ids, true);
	               $hasIndexBis = true;
	           }
	       }	       
	       if(empty($articles))
	           $articles = [];
	    }
	    else 
	        $articles = [];
	    $scrollable = count($articles) ? 'true' : 'false';
	    $vars = array(
	        'scrollable' => $scrollable, 'articles' => $articles, 'search' => $userInput,
	        'index' => $index, 'search_by' => 'form.title',
	        'ids' => serialize($ids)
	    );
	    if(isset($hasIndexBis) && $hasIndexBis != null) {
	        $vars['index_bis'] = $indexBis;
	        $vars['total_bis'] = $totalBis;
	        $vars['total'] = 0;
	    } else {
	        $vars['total'] = isset($total) ? $total : 0;
	    }
	    return $this->doRender($request, 'search.html.twig', $vars);	    
	}
	*/
}