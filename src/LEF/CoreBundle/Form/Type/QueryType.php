<?php
// src/LEF/CoreBundle/Form/Type/QueryType.php

namespace LEF\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Parameter;
use LEF\CoreBundle\Form\ChoiceList\ORMQueryLoader;


class QueryType extends EntityType {
    public function getQueryPartsForCachingHash(Query $query) {
        return array(
            $query->getSQL(),
            array_map(array($this, 'parameterToArray'), $query->getParameters()->toArray())
        );
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $choiceLoader = function (Options $options) {
            // Unless the choices are given explicitly, load them on demand
            if (null === $options['choices']) {
                $hash = null;
                $qbParts = null;
                $qParts = null;
                
                // If there is no QueryBuilder we can safely cache DoctrineChoiceLoader,
                // also if concrete Type can return important QueryBuilder parts to generate
                // hash key we go for it as well
                if ((!$options['query_builder'] || false !== ($qbParts = $this->getQueryBuilderPartsForCachingHash($options['query_builder'])))
                    && (!$options['query'] || false !== ($qParts = $this->getQueryPartsForCachingHash($options['query'])))) {
                    $hash = CachingFactoryDecorator::generateHash(array(
                        $options['em'],
                        $options['class'],
                        $qbParts,
                    ));
                        
                    if (isset($this->choiceLoaders[$hash])) {
                        return $this->choiceLoaders[$hash];
                    }
                 
                }
                
                if (null !== $options['query_builder']) {
                    $entityLoader = $this->getLoader($options['em'], $options['query_builder'], $options['class']);
                } elseif (null !== $options['query']) {
                    $entityLoader = $this->getLoader($options['em'], $options['query'], $options['class']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $this->getLoader($options['em'], $queryBuilder, $options['class']);
                }    
                
                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                    );
                
                if (null !== $hash) {
                    $this->choiceLoaders[$hash] = $doctrineChoiceLoader;
                }
                
                return $doctrineChoiceLoader;
            }           
        };
        
        $resolver->setDefaults(array(
            'query' => null,
            'choice_loader' => $choiceLoader
        ));
        
        $queryNormalizer = function (Options $options, $query) {
            if (is_callable($query)) {
                $query = call_user_func($query, $options['em']->getRepository($options['class']));
                
                if(null !== $query && !$query instanceof Query)
                    throw new UnexpectedTypeException($query, 'Doctrine\ORM\Query');
            }
            
            return $query;
        };
        
        $resolver->setNormalizer('query', $queryNormalizer);
        $resolver->setAllowedTypes('query', array('null', 'callable', 'Doctrine\ORM\Query'));
    } 
    
    public function getLoader(ObjectManager $manager, $query, $class) {
        return $query instanceof QueryBuilder ? new ORMQueryBuilderLoader($query) : new ORMQueryLoader($query);
    }
    
    public function getBlockPrefix() {
        return 'query';
    }
    
    private function parameterToArray(Parameter $parameter) {
        return array($parameter->getName(), $parameter->getType(), $parameter->getValue());
    }
}