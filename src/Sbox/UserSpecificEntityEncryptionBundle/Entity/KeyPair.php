<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Sbox\CryptoBundle\KeyDerivation\ExpensiveKeyDerivation;
use Sbox\CryptoBundle\PublicKeyCryptography\PublicKeyCryptography;
use Sbox\CryptoBundle\Randomness\RandomGenerator;
use Sbox\CryptoBundle\SecretKeyCryptography\AuthenticatedSecretKeyCryptography;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class KeyPair
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="public_key", type="binarystring")
     */
    protected $publicKey;

    /**
     * @var string
     * @ORM\Column(name="encrypted_secret_key", type="binarystring")
     */
    protected $encryptedSecretKey;

    /**
     * @var string
     * @ORM\Column(name="master_secret_salt", type="binarystring")
     */
    protected $masterSecretSalt;

    /**
     * This property is used to cache the master secret in memory in a single request thread.
     * @var string
     */
    protected $cachedMasterSecret;

    /**
     * @var  UserInterface
     */
    protected $user;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="EncryptedItemKey", mappedBy="keyPair")
     */
    protected $encryptedItemKeys;

    /** @var  \DateTime */
    protected $createdAt;

    /**
     * KeyPair constructor.
     */
    public function __construct()
    {
        $this->id = '';
        $this->publicKey = '';
        $this->encryptedSecretKey = '';
        $this->masterSecretSalt = RandomGenerator::generateRandomBytes(ExpensiveKeyDerivation::SALT_LENGTH);
        $this->cachedMasterSecret = '';
        $this->encryptedItemKeys = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Generates a new, random key pair and encrypts the secret key with a secret derived securely from the user's
     * password.
     * @param string $plainPassword
     */
    public function generateKeyPair(string $plainPassword): void
    {
        // Generate a random, plain key pair.
        $plainKeyPair = PublicKeyCryptography::generateKeyPair();

        // Extract the public and the secret key from the key pair.
        $publicKey = PublicKeyCryptography::getPublicKeyFromKeyPair($plainKeyPair);
        $plainSecretKey = PublicKeyCryptography::getSecretKeyFromKeyPair($plainKeyPair);

        $masterSecret = $this->getMasterSecretFromPassword($plainPassword);

        // Symmetrically encrypt the secret key using the master secret and authenticated encryption.
        $aead = AuthenticatedSecretKeyCryptography::constructWithSecret($masterSecret);
        $encryptedSecretKey = $aead->encrypt($plainSecretKey);

        // Persist the public key and the encrypted secret key in the KeyPair entity.
        $this->publicKey = $publicKey;
        $this->encryptedSecretKey = $encryptedSecretKey;
    }

    public function getMasterSecretFromPassword(string $plainPassword): string
    {
        // Derive the master secret from the plain password and the master secret salt.
        return ExpensiveKeyDerivation::deriveKeyFromPassword(
            AuthenticatedSecretKeyCryptography::SECRET_LENGTH,
            $plainPassword,
            $this->masterSecretSalt
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     */
    public function getEncryptedSecretKey(): string
    {
        return $this->encryptedSecretKey;
    }

    /**
     * @param string $encryptedSecretKey
     */
    public function setEncryptedSecretKey(string $encryptedSecretKey): void
    {
        $this->encryptedSecretKey = $encryptedSecretKey;
    }

    /**
     * @return string
     */
    public function getMasterSecretSalt(): string
    {
        return $this->masterSecretSalt;
    }

    /**
     * @param string $masterSecretSalt
     */
    public function setMasterSecretSalt(string $masterSecretSalt): void
    {
        $this->masterSecretSalt = $masterSecretSalt;
    }

    /**
     * @return string
     */
    public function getCachedMasterSecret(): string
    {
        return $this->cachedMasterSecret;
    }

    /**
     * @param string $cachedMasterSecret
     */
    public function setCachedMasterSecret(string $cachedMasterSecret): void
    {
        $this->cachedMasterSecret = $cachedMasterSecret;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection
     */
    public function getEncryptedItemKeys(): ArrayCollection
    {
        return $this->encryptedItemKeys;
    }

    /**
     * @param EncryptedItemKey $encryptedItemKey
     */
    public function addEncryptedItemKey(EncryptedItemKey $encryptedItemKey): void
    {
        $this->encryptedItemKeys->add($encryptedItemKey);
    }

    /**
     * @param EncryptedItemKey $encryptedItemKey
     */
    public function removeEncryptedItemKey(EncryptedItemKey $encryptedItemKey): void
    {
        $this->encryptedItemKeys->removeElement($encryptedItemKey);
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
