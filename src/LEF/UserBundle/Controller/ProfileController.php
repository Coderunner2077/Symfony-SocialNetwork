<?php
// src/LEF/UserBundle/Controller/ProfileController.php

namespace LEF\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\LogicException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\GroupBundle\Bitmask\PrivilegeBitmasks;
use LEF\UserBundle\Form\Type\AvatarType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileController extends Controller {
    use ControllerTrait;
    public $pathPrefix = 'LEFUserBundle\\Profile\\';
    public function showAction($id, Request $request) {
        $limit = $this->container->getParameter('group_posts_limit');
        $index = $request->query->has('index') ? $request->query->get('index') : 0;
        $offset = $index * $limit;
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFUserBundle:User');
        
        if(empty($id)) {
            $user = $this->getUser();
            if (!is_object($user) || !$user instanceof UserInterface) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }
            
            $id = $user->getId();
        } else
            $user = $rep->findOneWithAll($id);
        
        if(!$this->get('authentication_context')->isAuthenticated() 
            || $this->get('post_authorization')->isBlocker($user))
            return $this->render('LEFUserBundle\Profile\show.html.twig', array(
                'user' => $user, 'id' => $id));
        
        $posts = $em->getRepository('LEFPostBundle:Post')
                    ->myFindByAuthor($id, array('publishedAt' => 'DESC'), $limit, $offset);
        $scrollable = count($posts) > 0 ? 'true' : 'false';
        $vars = array(
            'scrollable' => $scrollable,
            'posts' => $posts,
            'total' => count($posts),
            'index' => $index,
            'id' => $id
        ); 
        if($index == 0 && $user == $this->getUser())
            $vars['subresponse'] = $this->forward('LEF\PostBundle\Controller\DefaultController::addAction', array(),
                array('wall' => true)
            )->getContent();
                
        if(!empty($user))
            $vars['user'] = $user;
        if($index == 0) {
            $nbGroups = $em->getRepository('LEFGroupBundle:MemberPrivilege')
                           ->countPrivileges($id, PrivilegeBitmasks::MEMBER);
            $vars['isMember'] = $nbGroups > 0;
        }
        return $this->doRender($request, 'show.html.twig', $vars);
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function changeAvatarAction(Request $request) {
        $user = $this->getUser();
        $form = $this->get('form.factory')->create(AvatarType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            
            $request->getSession()->getFlashbag()->add('success', 'flash.modified.avatar');
            return $this->redirectToRoute('lef_user_profile_show');
        }
        
        return $this->render('LEFUserBundle\Profile\changeAvatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAvatarAction(Request $request) {
        $user = $this->getUser();
        if(empty($user->getAvatar()))
            throw new LogicException('exception.logic.lefuser.delete_avatar');
        $form = $this->get('form.factory')->create();
        $form->handleRequest($request);
        $id = 'avatar-' . $user->getAvatar()->getId(); 
        if($form->isSubmitted() && $form->isValid()) {
            $avatar = $user->getAvatar();
            $user->setAvatar(null);
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->remove($avatar);
            $em->flush();
            $vars = array('id' => $id, 'alert' => true, 'alertStatus' => 'success', 
                'alertText' => 'flash.deleted.avatar');
            return $this->doRender($request, 'lef_user_profile_show', array(), $vars);
        }
        
        return $this->doRender($request, 'deleteAvatar.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }
    
    /**
     * @Security("has_role('ROLE_USER')") 
     */
    public function showDataAction(Request $request) {
        return $this->doRender($request, 'showData.html.twig', array('user' => $this->getUser()));
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function retrieveDataAction(Request $request) {
        $user = $this->getUser();
        if($request->query->has('json')) {
            $trans = $this->get('translator');
            $json = new JsonResponse(null, 200, 
                ['Content-Disposition' => 'attachment; filename="user_profile_data.json"',
               
            ]);
            //return $json;
            $view = $this->renderView('LEFUserBundle\Profile\retrieveData.json.twig', array('user' => $user));
            $json->setContent($view);
            return $json;
            $response =  $this->render('LEFUserBundle\Profile\retrieveData.json.twig', array('user' => $user));
            $response->headers->add(array(
                'Content-Disposition' => 'attachment', 'Content-Type' => 'application/json'));
            return $response;
        }
        $html = $this->renderView('LEFUserBundle\Profile\retrieveData.html.twig', array(
            'user' => $this->getUser()
        ));
        
        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'user_profile_data.pdf'
        );
    }
}