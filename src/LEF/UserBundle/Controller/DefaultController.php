<?php

namespace LEF\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use LEF\CoreBundle\Controller\ControllerTrait;
use LEF\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use LEF\CoreBundle\Component\Search\Object\SearchUser;
use LEF\UserBundle\Form\Type\SearchUserType;

class DefaultController extends Controller
{
    use ControllerTrait;
    
    public $pathPrefix = 'LEFUserBundle\\Default\\';
    public function indexAction()
    {
        return $this->render('LEFUserBundle:Default:index.html.twig');
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function followAction(User $followed, Request $request) {
        if($followed->getId() == $this->getUser()->getId())
            throw new LogicException('exception.logic.lefuser.follow_self');
        $userSession = $this->get('lef_user_session');
        $id = $followed->getId();
        if($userSession->isFollowed($id) == true)
            throw new LogicException('exception.logic.lefuser.follow');
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();
        $isFollowed = $em->getRepository('LEFUserBundle:User')->isUserFollowed($user->getId(), $id);
        if($isFollowed)
            throw new LogicException('exception.logic.lefuser.follow');
        $user->addFollowed($followed);
        $em->persist($user);
        $em->flush();
        $userSession->addFollowed($id);
        return $this->doRender($request, 'lef_user_profile_show', array('id' => $id),
            array('alert' => true, 'alertStatus' => 'success',
                  'alertText' => 'flash.followed.user',
                  'url' => $this->generateUrl('lef_user_unfollow', array('id' => $id)),
                  'text' => $this->get('translator')->trans('lefcore.unfollow'),
                  'nbFollowers' =>  $this->get('number_shower')->showNumber($followed->getNbFollowers()),
                  'dataFollow' => 'user-' . $id
            )
        );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     */
    public function unfollowAction(User $unfollowed, Request $request) {
        $userSession = $this->get('lef_user_session');
        $id = $unfollowed->getId();
        if($userSession->isFollowed($id) == false)
            throw new LogicException('exception.logic.lefuser.unfollow');
            
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();
        $isFollowed = $em->getRepository('LEFUserBundle:User')->isUserFollowed($user->getId(), $id);
        if($isFollowed == false)
            throw new LogicException('exception.logic.lefuser.unfollow');
        $user->removeFollowed($unfollowed);
        $em->persist($user);
        $em->flush();
        $userSession->removeFollowed($id);
        return $this->doRender($request, 'lef_user_profile_show', array('id' => $id),
            array('alert' => true, 'alertStatus' => 'info',
                'alertText' => 'flash.unfollowed.user',
                'url' => $this->generateUrl('lef_user_follow', array('id' => $id)),
                'text' => $this->get('translator')->trans('lefcore.follow'),
                'nbFollowers' =>  $this->get('number_shower')->showNumber($unfollowed->getNbFollowers()),
                'dataFollow' => 'user-' . $id
            )
        );
    }
    
    /**
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("user", options={"repository_method": "findOneWithAll"})
     */
    public function showGroupsAction(User $user, Request $request) {
        $em = $this->get('doctrine.orm.entity_manager');
        $rep = $em->getRepository('LEFGroupBundle:MemberPrivilege');
        $privileges = $rep->findPrivileges($user->getId());
      
        return $this->render('LEFUserBundle\Default\showGroups.html.twig', array(
            'privileges' => $privileges, 'user' => $user
        ));
    }
    
    public function searchAction($index, Request $request) {
        $search = new SearchUser();
        if($request->query->has('input') || $request->request->has('input'))
            $search->setInput($request->query->has('input')
                ? $request->query->get('input') : $request->request->get('input'));
            
        $form = $this->get('form.factory')->create(SearchUserType::class, $search);
        $form->handleRequest($request);
        $isAjax = $request->query->has('_token') && $this->isCsrfTokenValid('search_item', $request->query->get('_token'));
        $searcher = $this->get('lef_core.component.search.searcher');
        if(($form->isSubmitted() && $form->isValid()) || $isAjax) {
            $search->processInput();
            //throw new \RuntimeException('voila : ' . print_r($search, true));
            $vars = $searcher->searchUsers($search, $index, $request);
            $vars['form'] = $form->createView();
                
            return $this->doRender($request, 'search.html.twig', $vars);
        }
        else {
            $search->processInput();
            $vars = $searcher->searchUsers($search, $index, $request);
            $vars['form'] = $form->createView();
            return $this->doRender($request, 'search.html.twig', $vars);
        }            
    }
}
