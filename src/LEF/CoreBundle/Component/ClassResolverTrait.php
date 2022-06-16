<?php
// src/LEF/CoreBundle/Component/ClassResolverTrait;

namespace LEF\CoreBundle\Component;

trait ClassResolverTrait {
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