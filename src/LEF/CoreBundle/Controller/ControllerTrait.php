<?php
// src/LEF/CoreBundle/Controller/ControllerTrait;

namespace LEF\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ControllerTrait {    
    public function redirectToTargetPath(Request $request) {
        $router = $this->get('router');
        $targetPath = $request->request->has('_target_path') ? $request->request->get('_target_path') 
                        : $request->query->get('_target_path');
        $relativePath = substr($targetPath, strpos($targetPath, '/web/') + 4);
        
        if($router->match($relativePath))
            return $this->redirect($targetPath);
    }
    
    protected function doRender(Request $request, $path, array $vars = array(), array $jsonData = array()) {
        if($request->isXmlHttpRequest()) {
            foreach($jsonData as $key => &$value) {
                if($key != 'alertStatus' && $key != 'id' && is_string($value))
                    $value = $this->get('translator')->trans($value);
            }
            if(!empty($jsonData))
                return new JsonResponse($jsonData);
           
            $path = preg_match('#\.twig$#', $path) ? 'ajax\\' . $path : $path;
        }
        
        if(isset($jsonData['alertStatus']))
            $request->getSession()->getFlashBag()
            ->add($jsonData['alertStatus'], $jsonData['alertText']);
        
        if($request->request->has('_target_path')) //  ?
            return $this->redirectToTargetPath($request);
        
        if(!preg_match('#.twig$#', $path))
            return $this->redirectToRoute($path, $vars);
        $path = $this->pathPrefix . $path;
                
        return $this->render($path, $vars);
    }
    
    public function resolveClass(&$attributeName) {
        switch($attributeName) {
            case 'article':
                $class = 'LEF\ArticleBundle\Entity\Article';
                break;
            case 'comment':
                $class = 'LEF\ArticleBundle\Entity\Comment';
                break;
            case 'group-post':
                $class = 'LEF\GroupBundle\Entity\GroupPost';
                $attributeName = 'groupPost';
                break;
            case 'post':
                $class = 'LEF\PostBundle\Entity\Post';
                break;
            default:
                $class = null;
        }
        return $class;
    }
}