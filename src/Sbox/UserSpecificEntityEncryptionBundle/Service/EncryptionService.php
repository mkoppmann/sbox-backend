<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Service;

use Doctrine\ORM\EntityRepository;
use Sbox\CryptoBundle\PublicKeyCryptography\PublicKeyCryptography;
use Sbox\CryptoBundle\Randomness\RandomGenerator;
use Sbox\CryptoBundle\SecretKeyCryptography\AuthenticatedSecretKeyCryptography;
use Sbox\UserBundle\Entity\User;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptableEntity;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptedItemKey;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPair;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPairUserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EncryptionService
{
    /** @var  TokenStorageInterface */
    protected $tokenStorage;

    /**
     * EncryptionService constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Randomly generates an item key, asymmetrically encrypts it with the target user's public key and returns the
     * plain key.
     * @param KeyPairUserInterface $user
     * @param EncryptableEntity $entity
     * @return null|EncryptedItemKey
     */
    public function createEncryptedItemKeyForUserAndEntity(
        KeyPairUserInterface $user,
        EncryptableEntity $entity
    ): ?EncryptedItemKey {
        if (!$user) {
            return null;
        }

        $keyPair = $this->getKeyPairOfUser($user);

        if (!$keyPair) {
            return null;
        }

        // Randomly generate an item key.
        $plainKey = RandomGenerator::generateRandomBytes(AuthenticatedSecretKeyCryptography::KEY_LENGTH);

        // Asymmetrically encrypt the plain key with the target user's public key.
        $publicKeyCryptography = new PublicKeyCryptography();
        $publicKeyCryptography->setPublicKey($keyPair->getPublicKey());
        $encryptedKey = $publicKeyCryptography->encrypt($plainKey);

        // Create an EncryptedItemKey instance and persist it.
        $encryptedItemKey = new EncryptedItemKey();
        $encryptedItemKey->setPlainKey($plainKey); // Will not be persisted.
        $encryptedItemKey->setEncryptedKey($encryptedKey);
        $encryptedItemKey->setKeyPair($keyPair);
        $encryptedItemKey->setEncryptableEntity($entity);
        $entity->addEncryptedItemKey($encryptedItemKey);

        return $encryptedItemKey;
    }

    /**
     * @param EncryptableEntity $entity
     * @return null|EncryptedItemKey
     */
    public function createEncryptedItemKeyForCurrentUser(EncryptableEntity $entity): ?EncryptedItemKey
    {
        $currentUser = $this->getCurrentUser();

        if ($currentUser) {
            return $this->createEncryptedItemKeyForUserAndEntity($currentUser, $entity);
        }
    }

    /**
     * @param EncryptedItemKey $encryptedItemKey
     * @param User $recipient
     * @return null|EncryptedItemKey
     */
    public function createEncryptedItemKeyForAnotherUser(
        EncryptedItemKey $encryptedItemKeySender,
        User $recipient
    ): ?EncryptedItemKey {
        if (!$recipient || !$encryptedItemKeySender) {
            return null;
        }

        $sender = $this->getCurrentUser();

        if (!$sender) {
            return null;
        }
        $keyPairSender = $this->getKeyPairOfUser($sender);
        $keyPairRecipient = $this->getKeyPairOfUser($recipient);

        if (!$keyPairRecipient || !$keyPairSender) {
            return null;
        }

        // Randomly generate an item key.
        $plainKey = $encryptedItemKeySender->getPlainKey();


        // Asymmetrically encrypt the plain key with the target user's public key.
        $publicKeyCryptography = new PublicKeyCryptography();
        $publicKeyCryptography->setPublicKey($keyPairRecipient->getPublicKey());
        $encryptedKey = $publicKeyCryptography->encrypt($plainKey);

        // Create an EncryptedItemKey instance and persist it.
        $encryptedItemKey = new EncryptedItemKey();
        $encryptedItemKey->setPlainKey($plainKey); // Will not be persisted.
        $encryptedItemKey->setEncryptedKey($encryptedKey);
        $encryptedItemKey->setKeyPair($keyPairRecipient);
        $encryptedItemKey->setEncryptableEntity($encryptedItemKeySender->getEncryptableEntity());
        $encryptedItemKeySender->getEncryptableEntity()->addEncryptedItemKey($encryptedItemKey);#


        return $encryptedItemKey;
    }

    /**
     * @param KeyPairUserInterface $user
     * @param EncryptableEntity $entity
     * @return null|EncryptedItemKey
     */
    public function getEncryptedItemKeyForUser(KeyPairUserInterface $user, EncryptableEntity $entity): ?EncryptedItemKey
    {
        if (!$user) {
            return null;
        }

        $keyPair = $this->getKeyPairOfUser($user);

        if (!$keyPair) {
            return null;
        }

        $encryptedItemKey = $entity->getEncryptedItemKeyForKeyPair($keyPair);

        if (!$encryptedItemKey) {
            return null;
        }

        // Symmetrically decrypt the secret key using the master secret and authenticated encryption.
        $masterSecret = $keyPair->getCachedMasterSecret();
        $aead = AuthenticatedSecretKeyCryptography::constructWithSecret($masterSecret);
        $plainSecretKey = $aead->decrypt($keyPair->getEncryptedSecretKey());

        // Asymmetrically decrypt the plain key with the target user's secret key.
        $publicKeyCryptography = new PublicKeyCryptography();
        $publicKeyCryptography->setSecretKey($plainSecretKey);
        $decryptedKey = $publicKeyCryptography->decrypt($encryptedItemKey->getEncryptedKey());

        // Set the plain item key in memory.
        $encryptedItemKey->setPlainKey($decryptedKey);

        return $encryptedItemKey;
    }

    /**
     * @param EncryptableEntity $entity
     * @return null|EncryptedItemKey
     */
    public function getEncryptedItemKeyForCurrentUser(EncryptableEntity $entity): ?EncryptedItemKey
    {
        $currentUser = $this->getCurrentUser();

        if ($currentUser) {
            return $this->getEncryptedItemKeyForUser($currentUser, $entity);
        }
    }

    /**
     * Get the logged-in user or null.
     *
     * @return KeyPairUserInterface|null
     */
    protected function getCurrentUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        if (!$user instanceof KeyPairUserInterface) {
            return;
        }

        return $user;
    }

    /**
     * @param KeyPairUserInterface $user
     * @return null|KeyPair
     */
    protected function getKeyPairOfUser(KeyPairUserInterface $user): ?KeyPair
    {
        return $user->getKeyPair();
    }
}
