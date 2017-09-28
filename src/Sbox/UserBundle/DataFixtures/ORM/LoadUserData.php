<?php

namespace Sbox\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sbox\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * LoadUserData constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        if (isset($container)) {
            $this->container = $container;
        }
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $encoder = $this->container->get('security.password_encoder');

        $plainTextPassword = 'TestP@ssw0rd!';

        $userUnprivileged = new User();
        $userUnprivileged->setUsername('testuser');
        $password = $encoder->encodePassword($userUnprivileged, $plainTextPassword);
        $userUnprivileged->setPassword($password);
        $userUnprivileged->setPlainTextPassword($plainTextPassword);
        $userUnprivileged->setEmail('test@example.com');
        $userUnprivileged->setName('Unprivileged User');

        $manager->persist($userUnprivileged);
        $manager->flush();

        $userAdmin = new User();
        $userAdmin->setUsername('testadmin');
        $password = $encoder->encodePassword($userAdmin, $plainTextPassword);
        $userAdmin->setPassword($password);
        $userAdmin->setPlainTextPassword($plainTextPassword);
        $userAdmin->setEmail('test@example.com');
        $userAdmin->setName('Admin User');

        $manager->persist($userAdmin);
        $manager->flush();
    }
}
