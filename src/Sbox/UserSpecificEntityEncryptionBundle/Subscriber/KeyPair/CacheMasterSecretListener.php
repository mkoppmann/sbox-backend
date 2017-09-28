<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\KeyPair;

use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPair;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPairUserInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class CacheMasterSecretListener
{
    const MASTER_SECRET_SESSION_ATTRIBUTE_NAME = 'master_secret';

    /** @var  TokenStorage */
    protected $tokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param InteractiveLoginEvent $event
     * @psalm-suppress PossiblyNullReference
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var KeyPairUserInterface $user */
        $user = $event->getAuthenticationToken()->getUser();

        if ($user && $user instanceof KeyPairUserInterface && $user->getKeyPair()) {
            $plainTextPassword = $event->getRequest()->get('password');
            $masterSecret = $user->getKeyPair()->getMasterSecretFromPassword($plainTextPassword);

            $session = $event->getRequest()->getSession();

            if ($session) {
                $session->set(self::MASTER_SECRET_SESSION_ATTRIBUTE_NAME, $masterSecret);
            }

            // Cache the master secret in the user's KeyPair object (in memory).
            $user->getKeyPair()->setCachedMasterSecret($masterSecret);
        }
    }

    /**
     * @param GetResponseEvent $event
     * @psalm-suppress PossiblyNullReference
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        /** @var KeyPairUserInterface $user */
        $token = $user = $this->tokenStorage->getToken();

        if ($token) {
            $user = $token->getUser();

            if ($user && $user instanceof KeyPairUserInterface && $user->getKeyPair() && $session
                && $session->get(self::MASTER_SECRET_SESSION_ATTRIBUTE_NAME)) {
                $user->getKeyPair()->setCachedMasterSecret($session->get(self::MASTER_SECRET_SESSION_ATTRIBUTE_NAME));
            }
        }
    }
}
