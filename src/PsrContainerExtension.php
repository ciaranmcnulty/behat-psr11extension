<?php
declare(strict_types=1);

namespace Roave\BehatPsrContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class PsrContainerExtension implements Extension
{
    public function process(ContainerBuilder $container)
    {
    }

    public function getConfigKey()
    {
        return __NAMESPACE__;
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('container')->defaultValue('config/container.php')->end()
                ->scalarNode('name')->defaultValue('psr_container')->end()
            ->end()
        ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('roave.behat.psr.container.included.file', $config['container']);

        $definition = new Definition(ContainerInterface::class, ["%roave.behat.psr.container.included.file%"]);
        $definition->setFactory([ContainerFactory::class, 'createContainerFromIncludedFile']);
        $definition->addTag('helper_container.container');

        if (method_exists($definition, 'setShared')) {
            // introduced in Symfony 2.8
            $definition->setShared(false);
        }
        else {
            // removed in Symfony 3.0
            $definition->setScope(ContainerBuilder::SCOPE_PROTOTYPE);
        }

        $container->setDefinition($config['name'], $definition);
    }
}
