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
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wandi_easy_admin_plus');

        $this->addGeneratorSection($rootNode);
        $this->addTranslatorSection($rootNode);

        return $treeBuilder;
    }

    private function addTranslatorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('translator')
                    ->addDefaultsIfNotSet()
                    ->children()

                        ->variableNode('locales')
                            ->info('The user\'s locales to manage.')
                            ->defaultValue(array())
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return false === is_array($v);
                                })
                                ->thenInvalid('The locales option must be an array of user locale.')
                            ->end()
                        ->end()

                        ->variableNode('paths')
                            ->info('The translations\' paths.')
                            ->defaultValue(array())
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return false === is_array($v);
                                })
                                ->thenInvalid('The paths option must be an array of user paths.')
                            ->end()
                        ->end()

                        ->variableNode('excluded_domains')
                            ->info('The domains to exclude.')
                            ->defaultValue(array())
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return false === is_array($v);
                                })
                                ->thenInvalid('The excluded_domains option must be an array of user excluded domains.')
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end()
            ;
    }

    private function addGeneratorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('generator')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('dump_indentation')
                            ->defaultValue(4)
                        ->end()
                        ->integerNode('dump_inline')
                            ->defaultValue(6)
                        ->end()
                        ->scalarNode('name_backend')
                            ->cannotBeEmpty()
                            ->defaultValue('Back Office')
                        ->end()
                        ->scalarNode('translation_domain')
                            ->cannotBeEmpty()
                            ->defaultValue('EasyAdminPlusBundle')
                        ->end()
                        ->variableNode('bundles_filter')
                            ->defaultValue(['WandiEasyAdminPlusBundle'])
                            ->treatNullLike([])
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return false === is_array($v);
                                })
                                ->thenInvalid('The bundles_filter option must be an array of bundle names.')
                            ->end()
                        ->end()
                        ->append($this->getMethodsResolverNode())
                        ->append($this->getIconsResolverNode())
                        ->append($this->getFieldsResolverNode())
                        ->append($this->getSortResolverNode())
                        ->append($this->getAssetsResolverNode())
                    ->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    private function getMethodsResolverNode()
    {
        $defaultListMethods = [
            'new',
            'show',
            'edit',
            'delete',
        ];
        $defaultShowtMethods = [
            'edit',
            'delete',
        ];
        $defaultNewtMethods = [];
        $defaultEditMethods = [];
        $isArrayClosure = function ($v) {
            return false === is_array($v);
        };

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('methods');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('list')
                    ->info('The names of the actions enabled for the list page.')
                    ->defaultValue($defaultListMethods)
                    ->validate()
                        ->ifTrue($isArrayClosure)
                            ->thenInvalid('The list option must be an array of action names.')
                        ->ifTrue(self::validateMethodsClosure($defaultListMethods))
                            ->thenInvalid('Bad value of methods, accepted methods are '.implode(',', $defaultListMethods))
                        ->ifEmpty()
                            ->then(self::getMethodsClosure($defaultListMethods))
                    ->end()
                ->end()
                ->variableNode('show')
                    ->info('The names of the actions enabled for the show page.')
                    ->defaultValue($defaultShowtMethods)
                    ->validate()
                        ->ifTrue($isArrayClosure)
                            ->thenInvalid('The show option must be an array of action names.')
                        ->ifTrue(self::validateMethodsClosure($defaultShowtMethods))
                            ->thenInvalid('Bad value of methods, accepted methods are '.implode(',', $defaultShowtMethods))
                        ->ifEmpty()
                            ->then(self::getMethodsClosure($defaultShowtMethods))
                    ->end()
                ->end()
                ->variableNode('edit')
                    ->info('The names of the actions enabled for the edit page.')
                    ->defaultValue($defaultEditMethods)
                    ->validate()
                        ->ifTrue($isArrayClosure)
                            ->thenInvalid('The edit option must be an array of action names.')
                        ->ifTrue(self::validateMethodsClosure($defaultEditMethods))
                            ->thenInvalid('Bad value of methods, accepted methods are '.implode(',', $defaultEditMethods))
                        ->ifEmpty()
                            ->then(self::getMethodsClosure($defaultEditMethods))
                    ->end()
                ->end()
                ->variableNode('new')
                    ->info('The names of the actions enabled for the new page.')
                    ->defaultValue($defaultNewtMethods)
                    ->validate()
                        ->ifTrue($isArrayClosure)
                            ->thenInvalid('The new option must be an array of action names.')
                        ->ifTrue(self::validateMethodsClosure($defaultNewtMethods))
                            ->thenInvalid('Bad value of methods, accepted methods are '.implode(',', $defaultNewtMethods))
                        ->ifEmpty()
                            ->then(self::getMethodsClosure($defaultNewtMethods))
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private static function getMethodsClosure($methods)
    {
        return function () use ($methods) {
            return $methods;
        };
    }

    private static function validateMethodsClosure($supportedMethods)
    {
        return function ($methods) use ($supportedMethods) {
            return !empty(array_diff($methods, ['new', 'edit', 'show', 'delete']));
        };
    }

    private function getAssetsResolverNode()
    {
        $defaultJSValue = [
            '/bundles/cksourceckfinder/ckfinder/ckfinder.js',
        ];
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('assets');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('js')
                    ->defaultValue($defaultJSValue)
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return false === is_array($v);
                        })
                        ->thenInvalid('The js option must be an array of js file path.')
                    ->end()
                ->end()
                ->variableNode('css')
                    ->defaultValue([])
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return false === is_array($v);
                        })
                        ->thenInvalid('The css option must be an array of css file path.')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getSortResolverNode()
    {
        $defaultMethodsValue = [
            'list',
        ];
        $defaultPropertiesValue = [
            [
                'name' => 'position',
                'order' => 'ASC',
            ],
            [
                'name' => 'id',
                'order' => 'DESC',
            ],
        ];
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sort');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('methods')
                    ->defaultValue($defaultMethodsValue)
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return false === is_array($v);
                        })
                        ->thenInvalid('The methods option must be an array of method names.')
                    ->end()
                ->end()
                ->variableNode('properties')
                    ->defaultValue($defaultPropertiesValue)
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($properties) {
                            foreach ($properties as $property) {
                                if (false === is_array($property)
                                    || 2 != count($property)
                                    || !array_key_exists('name', $property)
                                    || !array_key_exists('order', $property)) {
                                    return true;
                                }
                            }
                        })
                        ->thenInvalid('Each property must be an array that contains the \'name\' and \'order\' indexes')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getFieldsResolverNode()
    {
        $defaultMethodsValue = [
            'new',
            'show',
        ];
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('fields');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('methods')
                    ->defaultValue($defaultMethodsValue)
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return $v === is_array($v);
                        })
                        ->thenInvalid('The actions option must be an array of action names.')
                    ->end()
                ->end()
                ->scalarNode('labels')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getIconsResolverNode()
    {
        $defaultActionsValue = [
            'new' => 'add',
            'show' => 'search',
            'edit' => 'edit',
            'delete' => 'trash',
        ];
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('icons');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('actions')
                    ->defaultValue($defaultActionsValue)
                    ->treatNullLike([])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return false === is_array($v) && null !== $v;
                        })
                        ->thenInvalid('The actions option must be an array.')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
