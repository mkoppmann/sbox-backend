<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\Encryption;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Sbox\CryptoBundle\GenericHashing\GenericHash;
use Sbox\CryptoBundle\SecretKeyCryptography\AuthenticatedSecretKeyCryptography;
use Sbox\MessageBundle\Entity\Message;
use Sbox\UserBundle\Entity\User;
use Sbox\UserSpecificEntityEncryptionBundle\Annotation\Encrypted;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptableEntity;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptedItemKey;
use Sbox\UserSpecificEntityEncryptionBundle\Exception\ItemNotDecryptableException;
use Sbox\UserSpecificEntityEncryptionBundle\Exception\ItemNotEncryptableException;
use Sbox\UserSpecificEntityEncryptionBundle\Service\EncryptedAnnotationHelper;
use Sbox\UserSpecificEntityEncryptionBundle\Service\EncryptionService;

/**
 * An abstract class that listens to Doctrine events that make encryption or decryption of data necessary.
 * @package Sbox\UserSpecificEntityEncryptionBundle\Subscriber\Encryption
 */
abstract class AbstractEncryptionDecryptionSubscriber
{
    /** @var  EncryptedAnnotationHelper */
    protected $encryptedAnnotationHelper;

    /** @var  EncryptionService */
    protected $encryptionService;

    /** @var array */
    protected $encryptableEntities;

    /** @var  array */
    protected $cleartextCache;

    /**
     * AbstractEncryptionDecryptionSubscriber constructor.
     * @param EncryptedAnnotationHelper $encryptedAnnotationHelper
     * @param EncryptionService $encryptionService
     * @param array $encryptableEntities
     */
    public function __construct(
        EncryptedAnnotationHelper $encryptedAnnotationHelper,
        EncryptionService $encryptionService,
        array $encryptableEntities
    ) {
        $this->encryptedAnnotationHelper = $encryptedAnnotationHelper;
        $this->encryptionService = $encryptionService;
        $this->encryptableEntities = $encryptableEntities;
        $this->cleartextCache = [];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        /** @var EncryptableEntity $object */
        $object = $args->getObject();

        if ($object instanceof EncryptableEntity &&
            $this->encryptedAnnotationHelper->isObjectEligibleForEncryption($object)) {
            // We create a new encrypted item key for the current user.
            $encryptedItemKey = $this->encryptionService->createEncryptedItemKeyForCurrentUser($object);

            if (!$encryptedItemKey) {
                throw new ItemNotEncryptableException(
                    'Item could not be encrypted. Either there is no current user or they do not have a key pair.'
                );
            }

            if ($object instanceof Message) {
                /**
                 * @var User[]
                 */
                $recipients = $object->getRecipients();
                foreach ($recipients as $recipient) {
                    $this->encryptionService->createEncryptedItemKeyForAnotherUser($encryptedItemKey, $recipient);
                }
            }

            $this->encryptItemWithEncryptedItemKey($object, $encryptedItemKey, $args->getEntityManager());
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @throws ItemNotEncryptableException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        /** @var EncryptableEntity $object */
        $object = $args->getObject();

        if ($object instanceof EncryptableEntity &&
            $this->encryptedAnnotationHelper->isObjectEligibleForEncryption($object)) {
            // Get the decrypted item key for the current user and the object.
            $itemKey = $this->encryptionService->getEncryptedItemKeyForCurrentUser($object);

            if (!$itemKey) {
                throw new ItemNotEncryptableException(
                    'Could not get the EncryptedItemKey for the already encrypted and to-be-updated Entity.'
                );
            }

            $this->encryptItemWithEncryptedItemKey($object, $itemKey, $args->getEntityManager());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var EncryptableEntity $object */
        $object = $args->getObject();

        if ($object instanceof EncryptableEntity &&
            $this->encryptedAnnotationHelper->isObjectEligibleForEncryption($object)) {
            $this->restoreCleartextOfEncryptedItemProperties($object);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var EncryptableEntity $object */
        $object = $args->getObject();

        if ($object instanceof EncryptableEntity &&
            $this->encryptedAnnotationHelper->isObjectEligibleForEncryption($object)) {
            $this->restoreCleartextOfEncryptedItemProperties($object);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ItemNotDecryptableException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        /** @var EncryptableEntity $object */
        $object = $args->getObject();

        if ($object instanceof EncryptableEntity &&
            $this->encryptedAnnotationHelper->isObjectEligibleForEncryption($object)) {
            $itemKey = $this->encryptionService->getEncryptedItemKeyForCurrentUser($object);

            if (!$itemKey) {
                throw new ItemNotDecryptableException(
                    'Could not get the EncryptedItemKey for the encrypted Entity.'
                );
            }

            $this->decryptItemWithEncryptedItemKey($object, $itemKey);
        }
    }

    /**
     * @param $object
     * @param EncryptedItemKey $encryptedItemKey
     * @param EntityManagerInterface $entityManager
     */
    protected function encryptItemWithEncryptedItemKey(
        EncryptableEntity $object,
        EncryptedItemKey $encryptedItemKey,
        EntityManagerInterface $entityManager
    ): void {
        $encryptedProperties = $this->encryptedAnnotationHelper->getEncryptedProperties($object);

        if (count($encryptedProperties) > 0) {
            // That object actually has to-be-encrypted properties. So let's go!
            /** @var \ReflectionProperty $encryptedProperty */
            foreach ($encryptedProperties as $encryptedProperty) {
                // We give the object an internal, in-memory ID so that we can reference it later within the same
                // thread. This is because the object does not yet have a database ID when it is about to be newly
                // inserted. Note that this id is not cryptographically random, but this is not important right here.
                // Speed, however, is important.
                if (!$object->getInternalId()) {
                    $object->setInternalId(uniqid('', true));
                }

                $encryptedProperty->setAccessible(true);

                $cleartextData = $encryptedProperty->getValue($object);

                // The nonce for the symmetric encryption is the keyed hash of the property name using the
                // object ID as the key.
                $nonce = GenericHash::hash(
                    $encryptedProperty->getName(),
                    AuthenticatedSecretKeyCryptography::NONCE_LENGTH
                );

                $ascc = new AuthenticatedSecretKeyCryptography($nonce, $encryptedItemKey->getPlainKey());
                $encryptedData = $ascc->encrypt($cleartextData);
                $encryptedProperty->setValue($object, $encryptedData);

                $entityManager->getUnitOfWork()->persist($encryptedItemKey);

                $this->cleartextCache[$object->getInternalId()][$encryptedProperty->getName()] = $cleartextData;
            }
        }
    }

    /**
     * @param EncryptableEntity $object
     * @param EncryptedItemKey $encryptedItemKey
     */
    protected function decryptItemWithEncryptedItemKey(
        EncryptableEntity $object,
        EncryptedItemKey $encryptedItemKey
    ): void {
        $encryptedProperties = $this->encryptedAnnotationHelper->getEncryptedProperties($object);

        if (count($encryptedProperties) > 0) {
            // That object actually has to-be-encrypted properties. So let's go!
            /** @var \ReflectionProperty $encryptedProperty */
            foreach ($encryptedProperties as $encryptedProperty) {
                $encryptedProperty->setAccessible(true);
                $encryptedValue = $encryptedProperty->getValue($object);

                // The nonce for the symmetric encryption is the keyed hash of the property name using the
                // object ID as the key.
                $nonce = GenericHash::hash(
                    $encryptedProperty->getName(),
                    AuthenticatedSecretKeyCryptography::NONCE_LENGTH
                );

                $ascc = new AuthenticatedSecretKeyCryptography($nonce, $encryptedItemKey->getPlainKey());
                $cleartextValue = $ascc->decrypt($encryptedValue);
                $encryptedProperty->setValue($object, $cleartextValue);
            }
        }
    }

    /**
     * Restores the cached cleartext values of all encrypted properties of the given object.
     * @param EncryptableEntity $object
     */
    protected function restoreCleartextOfEncryptedItemProperties(EncryptableEntity $object): void
    {
        /** @var array $cacheItem */
        $cacheItem = $this->cleartextCache[$object->getInternalId()];

        if ($object && $cacheItem) {
            $reflectionClass = new \ReflectionClass($object);

            foreach ($cacheItem as $property => $value) {
                $reflectionProperty = $reflectionClass->getProperty($property);

                if ($reflectionProperty) {
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($object, $value);
                    $reflectionProperty->setAccessible(false);
                }
            }
        }
    }
}
