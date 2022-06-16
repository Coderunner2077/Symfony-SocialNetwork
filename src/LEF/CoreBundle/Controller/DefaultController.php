<?php
// src/LEF/CoreBundle/Controller/DefaultController.php

namespace LEF\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\LogicException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LEF\CoreBundle\Entity\Entity;
use LEF\CoreBundle\Form\Type\ReportType;
use LEF\CoreBundle\Form\Type\ContactType;
use LEF\CoreBundle\Entity\Contact;
use LEF\UserBundle\Entity\User;
use LEF\UserBundle\Form\Type\AuthenticationFormType;

class DefaultController extends Controller {
    use ControllerTrait;
    
    public $pathPrefix = 'LEFCoreBundle\\Default\\';
    
    public function indexAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $topArticle = $em->getRepository('LEFArticleBundle:Article')->findWithAll(
            array('likes' => 'DESC', 'publishedAt' => 'DESC'), 1)->current();
        $topGroupPost = $em->getRepository('LEFGroupBundle:GroupPost')->findWithAll(
            array('likes' => 'DESC', 'publishedAt' => 'DESC'), 1)->current();
        $topPost = $em->getRepository('LEFPostBundle:Post')->findWithAll(
            array('likes' => 'DESC', 'publishedAt' => 'DESC'), 1)->current();
            $empty = (count($topArticle) || count($topGroupPost)  || count($topPost)) ? false : true;
        if($this->get('authentication_context')->isAuthenticated() === true && $empty)
            return $this->redirectToRoute('fos_user_profile_show');
        
        $loginForm = $this->get('form.factory')->create(AuthenticationFormType::class);
        //$login = $this->get('fos_user.security.controller')->loginAction($request)->getContent(); 
        return $this->render('LEFCoreBundle\Default\index.html.twig', array(
            'topArticle' => $topArticle,
            'topGroupPost' => $topGroupPost,
            'topPost' => $topPost,
            'loginForm' => $loginForm->createView()
        ));        
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function likeAction($id, $entity, Request $request) {
        if($request->isXmlHttpRequest() && !$request->isMethod('POST'))
            throw new AccessDeniedException('exception.accessdenied.lefcore.like');
        $data = $this->get('likes_manager')->processLike($id, $entity);
        
        if($request->isXmlHttpRequest()) {
            $response = new Response();
            $response->setContent(json_encode($data));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else if($request->query->has('_target_path')) {
            return $this->redirectToTargetPath($request);
        } else {
            return $this->redirectToRoute('lef_article_home');
        }
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function dislikeAction($id, $entity, Request $request) {
        if($request->isXmlHttpRequest() && !$request->isMethod('POST'))
            throw new AccessDeniedException('exception.accessdenied.lefcore.like');
        $data = $this->get('likes_manager')->processDislike($id, $entity);
            
        if($request->isXmlHttpRequest()) 
            return new JsonResponse($data);
        else if($request->query->has('_target_path')) {
            return $this->redirectToTargetPath($request);
        } else {
            return $this->redirectToRoute('lef_article_home');
        }
    }
    
    public function reportAction($id, $entity, Request $request) {
        if(!$this->get('authentication_context')->isUser())
            throw new AccessDeniedException('exception.accessdenied.lefcore.report');
        $attributeName = $entity;
        $class = $this->resolveClass($attributeName);
        if(empty($class))
            throw new LogicException('exception.logic.no_class');
        $em = $this->get('doctrine.orm.entity_manager');
        $theEntity = $em->getRepository($class)->find($id);
        if(!$theEntity instanceof Entity)
            throw new NotFoundHttpException('exception.notfound.lefcore.report');
                    
        $reportClass = $class . 'Report';
        $report = new $reportClass(['reporter' => $this->getUser(), $attributeName => $theEntity]);
        $form = $this->get('form.factory')->create(ReportType::class, $report);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($report);
            $em->flush();
            return $this->doRender($request, 'report.html.twig', array(), 
                array('alert' => true,
                'alertStatus' => 'info', 'alertText' => 'flash.reported.' . preg_replace('#-#', '_', $entity))
            );
        }
        return $this->doRender($request, 'report.html.twig', 
            array('form' => $form->createView(), 'id' => $theEntity->getId(), 'name' => $entity
        ));
    }
    
    public function contactAction(Request $request) {
        if($this->get('authentication_context')->isAuthenticated()) {
            $user = $this->getUser();
            $contact = new Contact(['id' => $user->getId(), 'name' => $user->getAlias(),
                'email' => $user->getEmail()
            ]);
        } else {
            $contact = new Contact();
        }
        
        $form = $this->get('form.factory')->create(ContactType::class, $contact);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $message = new \Swift_Message('Contact utilisateur', $contact->getMessage(), 'text');
            $message->addFrom($contact->getEmail(), $contact->getName())
                    ->addTo($this->getParameter('mailer_user'), $this->getParameter('app_webmaster'));
            $this->get('mailer')->send($message);
            $request->getSession()->getFlashBag()->add('success', 'flash.contacted');
            return $this->doRender($request, 'contact.html.twig', array('contacted' => true));
        };
        
        return $this->doRender($request, 'contact.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function blockAction(User $user, Request $request) {
        $currentUser = $this->getUser();
        if($user == $currentUser)
            throw new LogicException('exception.logic.block');
        $authChecker = $this->get('post_authorization');
        $isBlocked = $authChecker->isBlocked($user, $thorough = true);
        $form = $this->get('form.factory')->create();
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            if($isBlocked)
                $currentUser->removeBlocked($user);
            else
                $currentUser->addBlocked($user);
            $em->persist($currentUser);
            $em->flush();
            $session = $this->get('post_session');
            if($isBlocked)
                $session->removeBlocked($user->getId());
            else
                $session->addBlocked($user->getId());
            $id = 'user-' . $user->getId();
            $text = $this->get('translator')
                         ->trans($isBlocked ? 'lefcore.block' : 'lefcore.unblock');
            $html = '<i class="fas fa-' . ($isBlocked ? 'ban' : 'check-circle') .'"></i> '. $text;
            $class= 'form-caller blocker text-bold btn btn-' . ($isBlocked ? 'danger' : 'warning');
            return $this->doRender($request, 'lef_core_block', array('id' => $user->getId()),
                array('id' => $id, 'alert' => true,
                      'alertStatus' => 'warning', 
                      'alertText' => 'flash.'.($isBlocked ? 'un' : '') . 'blocked.user',
                      'target' => ['.blocker'], 'text' => [$html], 'classList' => [$class]
                )
            );
        }
        return $this->doRender($request, 'block.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'isBlocked' => $isBlocked
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function viewBlockedAction(Request $request) {
        $session = $this->get('post_session');
        $em = $this->get('doctrine.orm.entity_manager');
        $blocked = $em->getRepository('LEFUserBundle:User')
                      ->findUsers(array_values($session->getBlocked()));
        return $this->doRender($request, 'viewBlocked.html.twig',
            array('user' => $this->getUser(), 'blockedUsers' => $blocked)
        );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function viewBlockersAction(Request $request) {
        $session = $this->get('post_session');
        $session->startPostBlockedSession();
        $em = $this->get('doctrine.orm.entity_manager');
        $blockers = $em->getRepository('LEFUserBundle:User')
                      ->findUsers(array_values($session->getBlockers()));
        return $this->doRender($request, 'viewBlockers.html.twig', 
            array('user' => $this->getUser(), 'blockers' => $blockers)
        );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function showNotifsAction(Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFArticleBundle:ArticleNotification');
        $id = $this->getUser()->getId();
        $articleNotifs = $rep->findArticleNotifs($id);
        $groupPostNotifs = $rep->findGroupPostNotifs($id);
        $postNotifs = $rep->findPostNotifs($id);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function articleNotifsAction($index, Request $request) {
        $nombreNews = 3;
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFArticleBundle:ArticleNotification');
        $id = $this->getUser()->getId();
        $offset = $index * $nombreNews;
        $viewed = null;
        if($request->query->has('unseen')) {
            $viewed = false;
            $vars['unseen'] = 'only';
            $offset = 0;
        }
        
        $vars['articleNotifs'] = $articleNotifs =  $rep->findArticleNotifs($id, $offset, $viewed); 
        
        $notifSession = $this->get('notif_session');
        $flush = false;
        foreach($articleNotifs as $notif) {
            if(!$notif->isViewed()) {
                $flush = true;
                $notif->setViewed(true);
                if($notifSession->has('article', $notif->getArticle->getId(), false))
                    $notifSession->remove('t', $notif->getArticle()->getId());
            }
        }
        if($flush)
            $em->flush();
        $vars['scrollable'] = count($articleNotifs) ? 'true' : 'false';
        $vars['total'] = count($articleNotifs);
        $vars['user'] = $this->getUser();
        $vars['index'] = $index;
        return $this->doRender($request, 'articleNotifs.html.twig', $vars); 
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function groupPostNotifsAction($index, Request $request) {
        $nombrePosts = 3;
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:GroupPostNotification');
        $id = $this->getUser()->getId();
        $offset = $index * $nombrePosts;
        $viewed = null;
        if($request->query->has('unseen')) {
            $viewed = false;
            $offset = 0;
        }
        
        $groupPostNotifs = $rep->findGroupPostNotifs($id, $nombrePosts, $offset, $viewed);  
        $notifSession = $this->get('notif_session');
        $flush = false;
        foreach($groupPostNotifs as $notif) {
            if($notif->isViewed() === false) {
                $flush = true;
                $notif->setViewed(true);
                if($notifSession->has('group_post', $notif->getGroupPost()->getId(), false))
                    $notifSession->remove('group_post', $notif->getGroupPost()->getId());
            }            
        }
        if($flush)
            $em->flush();
        $scrollable = count($groupPostNotifs) ? 'true' : 'false';
        $vars = array(
            'scrollable' => $scrollable,
            'groupPostNotifs' => $groupPostNotifs,
            'total' => count($groupPostNotifs),
            'user' => $this->getUser(),
            'index' => $index
        );
        if($viewed !== null)
            $vars['unseen'] = 'only';
        return $this->doRender($request, 'groupPostNotifs.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function postNotifsAction($index, Request $request) {
        $nombrePosts = 3;
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFPostBundle:PostNotification');
        $id = $this->getUser()->getId();
        $offset = $index * $nombrePosts;
        $viewed = null;
        if($request->query->has('unseen')) {
            $viewed = false;
            $offset = 0;
        }
            
        $postNotifs = $rep->findPostNotifs($id, $nombrePosts, $offset, $viewed); 
        $notifSession = $this->get('notif_session');
        $flush = false;
        foreach($postNotifs as $notif) {
            if(!$notif->isViewed()) {
                $flush = true;
                $notif->setViewed(true);
                if($notifSession->has('post', $notif->getPost()->getId(), false))
                    $notifSession->remove('post', $notif->getPost()->getId());
            }
        }
        if($flush)
            $em->flush();
        $scrollable = count($postNotifs) ? 'true' : 'false';
        $vars = array(
            'scrollable' => $scrollable,
            'postNotifs' => $postNotifs,
            'total' => count($postNotifs),
            'user' => $this->getUser(),
            'index' => $index
        );
        if($viewed !== null)
            $vars['unseen'] = 'only';
        return $this->doRender($request, 'postNotifs.html.twig', $vars);
    }
    
    public function setLocaleAction($locale, Request $request) { 
        $url = $request->query->has('_target_path') ? $request->query->get('_target_path')
            : $this->generateUrl('lef_core_home');
       
        $response = new RedirectResponse($url);
        $response->headers->setCookie( 
            new Cookie('_locale', $locale, time() + (180 * 24 * 3600))//, '/', null, true, false, true)
        );
        return $response;
    }
    
    public function showTermsAction(Request $request) {
        return $this->render('LEFCoreBundle\Default\showTerms.html.twig');
    }
    
    public function showPolicyAction(Request $request) {
        return $this->render('LEFCoreBundle\Default\showPolicy.html.twig');
    }
    
    public function aboutAction() {
        return $this->render('LEFCoreBundle\Default\about.html.twig');
    }
}