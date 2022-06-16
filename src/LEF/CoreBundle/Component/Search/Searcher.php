<?php
// src/LEF/CoreBundle/Component/Search/Searcher.php

namespace LEF\CoreBundle\Component\Search;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use LEF\CoreBundle\Component\Search\Object\SearchArticle;  
use LEF\CoreBundle\Component\Search\Object\Search;
use LEF\CoreBundle\Component\Search\Object\SearchPost;
use LEF\CoreBundle\Component\Search\Object\SearchUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Searcher { 
    protected $em;
    protected $validator;
    protected $articlesLimit;
    
    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator, $articles_limit) {
        $this->em = $em;
        $this->validator = $validator;
        $this->articlesLimit = $articles_limit;
    }
    
    protected function inputVars(Search $search, $index, Request $request, $limit, $articles = false) {
        $vars = array();
        $vars['ids'] = $request->query->has('ids') ? unserialize($request->query->get('ids')) : array();
        $vars['limit'] = $limit = (int) $limit;
        $vars['offset'] = $index * $limit;
        $vars['rep'] = $this->em->getRepository($search->searchedClass());
        $vars['keywords'] = $search->getKeywords();
        $vars['errors'] = $this->validator->validate($search);
        
        if($articles) {
            $vars['indexBis'] = $indexBis = $request->query->has('index_bis') ? $request->query->get('index_bis') : 0;
            $vars['offsetBis'] = $indexBis * $limit;
            $vars['hasIndexBis'] = null;
        }
       
        return $vars;
    }
    public function searchArticles(SearchArticle $search, $index, Request $request) {
        extract($this->inputVars($search, $index, $request, $this->articlesLimit, true));
        $sort = $search->isMostLiked() ? array('likes' => 'DESC') : array('publishedAt' => 'DESC');
        //throw new \RuntimeException('voila : ' . print_r($search, true));
        if(!empty($keywords) && count($errors) == 0) {
            if($request->query->has('index_bis')) {
                $totalBis = $rep->countSearchResult($keywords, $search->getDays(), $ids, true); $hasIndexBis = true;
                $articles = $rep->searchArticles($keywords, $sort, $limit, $offsetBis, $search->getDays(), $ids, true);
            } else {
                $total = $rep->countSearchResult($keywords, $search->getDays());
                //throw new \RuntimeException('voila : ' . print_r($total, true));
                $articles = $rep->searchArticles($keywords, $sort, $limit, $offset, $search->getDays());
                $artIds = empty($articles) ? array() :
                array_map(function($article) {
                    return $article->getId();
                } , $articles);
                $ids = array_merge($ids, $artIds);
            }
            
            if(empty($articles) && $hasIndexBis == null && count($keywords) > 1) {
                $totalBis = $rep->countSearchResult($keywords, $search->getDays(), $ids, true); $hasIndexBis = true;
                $articles = $rep->searchArticles($keywords, $sort, $limit, $offsetBis, $search->getDays(), $ids, true);
                $hasIndexBis = true;
            }
        } 
        
        if(empty($articles))
            $articles = [];
            $vars = $this->outputVars($search->getInput(), $index, $articles, 'articles', $errors, 'form.title', $limit, $ids);
        if(isset($hasIndexBis) && $hasIndexBis != null) {
            $vars['index_bis'] = $indexBis;
            $vars['total_bis'] = $totalBis;
            $vars['total'] = 0;
        } else {
            $vars['total'] = isset($total) ? $total : 0;
        }
        $vars['ids'] = serialize($ids);
        //throw new \RuntimeException('voila : ' . print_r($search, true));
        
        return $vars;
    }
    
    protected function outputVars($input, $index, array $entities, $entityName, $errors, $searchBy, $limit, array $ids = array()) {
        $scrollable = count($entities) == $limit ? 'true' : 'false';
        $vars = array(
            'scrollable' => $scrollable, $entityName => $entities, 'input' => $input,
            'index' => $index, 'search_by' => $searchBy,
            'valid' => (count($errors) ? false : true)
        );
        return $vars;
    }
    
    public function searchPosts(SearchPost $search, $index, Request $request) {
        extract($this->inputVars($search, $index, $request, $this->articlesLimit));
        $sort = $search->isMostLiked() ? array('likes' => 'DESC') : array('publishedAt' => 'DESC');
        if(!empty($keywords) && count($errors) == 0) {
            $total = $rep->countSearchResult($keywords[0]);
            $posts = $rep->searchPosts($keywords[0], $sort, $limit, $offset);
            $postIds = empty($posts) ? array() :
            array_map(function($post) {
                return $post->getId();
            } , $posts);
            $ids = array_merge($ids, $postIds);
        }
        if(empty($posts))
            $posts = [];
        $vars = $this
        ->outputVars($search->getFormatedInput(), $index, $posts, 'posts', $errors, 'form.hashtag', $limit);
        $vars['total'] = empty($total) ? 0 : $total;
        return $vars;
    }
    
    public function searchUsers(SearchUser $search, $index, Request $request) {
        extract($this->inputVars($search, $index, $request, ($this->articlesLimit * 3)));
        $sort = $search->isUsername() ? array('username' => 'ASC') : 
            array('fullname' => 'ASC', 'username' => 'ASC');
        //throw new \RuntimeException('voila : ' . print_r(count($errors), true));
        if(!empty($keywords) && count($errors) == 0) {
            $total = $rep->countSearchResult($keywords[0], $search->isUsername());
            $users = $rep->searchUsers($keywords[0], $sort, $limit, $offset, $search->isUsername());
        } else {
            $users = [];
        }
        $searchBy = $search->isUsername() ? 'form.username' : 'form.username_fullname';
        $vars = $this
        ->outputVars($search->getFormatedInput(), $index, $users, 'users', $errors, $searchBy, $limit);
        $vars['total'] = empty($total) ? 0 : $total;
        
        return $vars;
    }
}