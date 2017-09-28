<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SboxUserSpecificEntityEncryptionBundleExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'sbox_user_specific_entity_encryption.user_class',
            $config['user_class']
        );

        $container->setParameter(
            'sbox_user_specific_entity_encryption.encryptable_entities',
            $config['encryptable_entities']
        );
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'sbox_user_specific_entity_encryption';
    }
}
