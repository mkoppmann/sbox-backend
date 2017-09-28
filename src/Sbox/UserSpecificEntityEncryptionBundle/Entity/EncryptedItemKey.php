<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sbox\CryptoBundle\PublicKeyCryptography\PublicKeyCryptography;
use Sbox\CryptoBundle\Randomness\RandomGenerator;

/**
 * @ORM\Entity
 */
class EncryptedItemKey
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * This property ist not persisted. It is only used to temporarily store the plain key in memory.
     * @var string
     */
    protected $plainKey;

    /**
     * @var string
     * @ORM\Column(name="encrypted_key", type="binarystring")
     */
    protected $encryptedKey;

    /**
     * @var KeyPair
     * @ORM\ManyToOne(targetEntity="KeyPair", inversedBy="encryptedItemKeys")
     * @ORM\JoinColumn(name="key_pair", referencedColumnName="id")
     */
    protected $keyPair;

    /**
     * @var EncryptableEntity
     * @ORM\ManyToOne(targetEntity="EncryptableEntity", inversedBy="encryptedItemKeys")
     * @ORM\JoinColumn(name="encryptable_entity", referencedColumnName="id")
     */
    protected $encryptableEntity;

    /**
     * EncryptedItemKey constructor.
     */
    public function __construct()
    {
        $this->id = '';
        $this->plainKey = '';
        $this->encryptedKey = '';
        $this->keyPair = new KeyPair();
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
     * @return KeyPair
     */
    public function getKeyPair(): KeyPair
    {
        return $this->keyPair;
    }

    /**
     * @param KeyPair $keyPair
     */
    public function setKeyPair(KeyPair $keyPair): void
    {
        $this->keyPair = $keyPair;
    }

    /**
     * @return EncryptableEntity
     */
    public function getEncryptableEntity(): EncryptableEntity
    {
        return $this->encryptableEntity;
    }

    /**
     * @param EncryptableEntity $encryptableEntity
     */
    public function setEncryptableEntity(EncryptableEntity $encryptableEntity): void
    {
        $this->encryptableEntity = $encryptableEntity;
    }

    /**
     * @return string
     */
    public function getPlainKey(): string
    {
        return $this->plainKey;
    }

    /**
     * @param string $plainKey
     */
    public function setPlainKey(string $plainKey): void
    {
        $this->plainKey = $plainKey;
    }

    /**
     * @return string
     */
    public function getEncryptedKey(): string
    {
        return $this->encryptedKey;
    }

    /**
     * @param string $encryptedKey
     */
    public function setEncryptedKey(string $encryptedKey): void
    {
        $this->encryptedKey = $encryptedKey;
    }
}
