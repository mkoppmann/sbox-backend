<?php

namespace Sbox\SessionBundle\DependencyInjection;

use Sbox\SessionBundle\Entity\Session;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class SboxDoctrineSessionHandlerExtension
 *
 * @package Sbox\SessionBundle\DependencyInjection
 * @author  Nikita Loges
 */
class SboxDoctrineSessionHandlerExtension extends Extension implements PrependExtensionInterface
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function prepend(ContainerBuilder $container): void
    {
        $doctrine = [
            'orm' => [
                'resolve_target_entities' => [
                    Session::class => Session::class,
                ]
            ]
        ];

        $container->prependExtensionConfig('doctrine', $doctrine);
    }
}
