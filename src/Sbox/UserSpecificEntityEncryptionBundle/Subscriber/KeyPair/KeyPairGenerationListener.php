<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\KeyPair;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPair;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPairUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class KeyPairGenerationListener
{
    /** @var  string */
    protected $userClass;

    public function __construct(string $userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        if (get_class($args->getObject()) === $this->userClass) {
            /** @var KeyPairUserInterface $user */
            $user = $args->getObject();

            $keyPair = new KeyPair();
            $keyPair->generateKeyPair($user->getPlainTextPassword());
            $keyPair->setUser($user);
            $user->setKeyPair($keyPair);

            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $uow->persist($keyPair);
        }
    }
}
