<?php

namespace Wandi\EasyAdminPlusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);

        $this->addGeneratorSection($rootNode);

        return $treeBuilder;
    }

    private function addGeneratorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('generator')
                    ->children()
                        ->integerNode( 'dump_indentation' )
                            ->defaultValue( 4 )
                        ->end()
                        ->integerNode( 'dump_inline' )
                            ->defaultValue( 6 )
                        ->end()
                        ->scalarNode( 'pattern_file' )
                            ->cannotBeEmpty()
                            ->defaultValue( 'config_easyadmin' )
                        ->end()
                        ->scalarNode( 'name_backend' )
                            ->cannotBeEmpty()
                            ->defaultValue( 4 )
                        ->end()
                        ->scalarNode( 'translation_domain' )
                            ->cannotBeEmpty()
                            ->defaultValue( 'EasyAdminPlusBundle' )
                        ->end()
                        ->scalarNode( 'bundles_filter' )
                            ->defaultValue( [
                                'EasyAdminPlusBundle',
                            ])
                        ->end()
                        ->append($this->getMethodsResolverNode())
                        ->append($this->getIconsResolverNode())
                        ->append($this->getFieldsResolverNode())
                        ->append($this->getSortResolverNode())
                        ->append($this->getAssetsResolverNode())
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function getAssetsResolverNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('assets');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode( 'js' )
                    ->defaultValue([
                        '/bundles/cksourceckfinder/ckfinder/ckfinder.js',
                    ])
                ->end()
                ->scalarNode( 'css' )
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getSortResolverNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sort');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode( 'methods' )
                    ->defaultValue([
                        'list',
                    ])
                ->end()
                ->scalarNode( 'properties' )
                    ->defaultValue([
                        [
                            'name' => 'position',
                            'order' => 'ASC'
                        ],
                        [
                            'name' => 'id',
                            'order' => 'DESC'
                        ],
                    ])
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getFieldsResolverNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('fields');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode( 'methods' )
                    ->defaultValue([
                        'new',
                        'show',
                    ])
                 ->end()
                ->scalarNode( 'label' )
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getIconsResolverNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('icons');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode( 'actions' )
                    ->defaultValue( [
                        'new' => 'add',
                        'show' => 'search',
                        'edit' => 'edit',
                        'delete' => 'trash'
                    ])
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getMethodsResolverNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('methods');

        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode( 'list' )
                        ->defaultValue( ['new', 'show', 'edit', 'delete'] )
                    ->end()
                    ->scalarNode( 'show' )
                        ->defaultValue( ['edit', 'delete'] )
                    ->end()
                    ->scalarNode( 'new' )
                        ->defaultValue( [] )
                    ->end()
                    ->scalarNode( 'edit' )
                        ->defaultValue( [] )
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

}
