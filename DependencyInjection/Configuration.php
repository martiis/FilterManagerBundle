<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ongr_filter_manager');

        $rootNode
            ->children()
                ->scalarNode('es_manager')
                    ->defaultValue('default')
                ->end()
            ->end();

        $this->addManagersSection($rootNode);
        $this->addFiltersSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addManagersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('managers')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('filters')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('repository')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addFiltersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('filters')
                    ->validate()
                        ->ifTrue(function ($v) {
                            $v = array_filter($v);

                            return empty($v);
                        })
                        ->thenInvalid('At least single filter must be configured.')
                    ->end()
                    // TODO: validate if filter names are unique
                    ->isRequired()
                    ->append($this->buildFilterTree('choice'))
                    ->append($this->buildFilterTree('match'))
                    ->append($this->buildFilterTree('sort'))
                    ->append($this->buildFilterTree('pager'))
                    ->append($this->buildFilterTree('document_field'))
                    ->append($this->buildFilterTree('range'))
                ->end()
            ->end();
    }

    /**
     * Builds filter config tree for given filter name
     *
     * @param string $filterName
     *
     * @return ArrayNodeDefinition
     */
    private function buildFilterTree($filterName)
    {
        $filter = new ArrayNodeDefinition($filterName);

        /** @var ParentNodeDefinitionInterface $node */
        $node = $filter
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->end()
                    ->arrayNode('relations')
                        ->children()
                            ->append($this->buildRelationsTree('search'))
                            ->append($this->buildRelationsTree('reset'))
                        ->end()
                    ->end()
                    ->scalarNode('request_field')->info('URL parameter name.')->isRequired()->end()
                    ->scalarNode('field')->info('Document field name.')->end()
                ->end();

        switch ($filterName) {
            case 'sort':
                $node
                    ->children()
                        ->arrayNode('choices')
                            ->prototype('array')
                                ->beforeNormalization()
                                    ->ifTrue(function ($v) {
                                        return empty($v['label']);
                                    })
                                    ->then(function ($v) {
                                        $v['label'] = $v['field'];

                                        return $v;
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->scalarNode('field')->isRequired()->end()
                                    ->scalarNode('order')->defaultValue('asc')->end()
                                    ->scalarNode('key')->info('Custom parameter value')->end()
                                    ->booleanNode('default')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end();
                break;
            case 'pager':
                $node
                    ->children()
                        ->integerNode('count_per_page')->info('Item count per page')->defaultValue(10)->end()
                    ->end();
                break;
        }

        return $filter;
    }

    /**
     * Builds relations config tree for given relation name.
     *
     * @param string $relationType
     *
     * @return ArrayNodeDefinition
     */
    private function buildRelationsTree($relationType)
    {
        $filter = new ArrayNodeDefinition($relationType);

        $filter
            ->validate()
                ->ifTrue(function ($v) {
                    return empty($v['include']) && empty($v['exclude']);
                })
                ->thenInvalid('Relation must have "include" or "exclude" fields specified.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) {
                    return !empty($v['include']) && !empty($v['exclude']);
                })
                ->thenInvalid('Relation must have only "include" or "exclude" fields specified.')
            ->end()
            ->children()
                ->arrayNode('include')
                    ->beforeNormalization()->ifString()->then(function ($v) {
                        return array($v);
                    })->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('exclude')
                    ->beforeNormalization()->ifString()->then(function ($v) {
                        return array($v);
                    })->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $filter;
    }
}
