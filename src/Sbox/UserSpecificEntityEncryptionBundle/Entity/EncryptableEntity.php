<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Entity;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @Serializer\Discriminator(disabled=true)
 * @Serializer\ExclusionPolicy("all")
 * @IgnoreAnnotation("psalm")
 */
abstract class EncryptableEntity
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * This property is used to give the entity a per-request unique ID so that it can be referenced before it is
     * persisted (it gets an ID only when it is persisted).
     * @var null|string
     */
    protected $internalId;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="EncryptedItemKey",
     *     mappedBy="encryptableEntity",
     *     fetch="EAGER",cascade={"persist","remove"})
     */
    protected $encryptedItemKeys;

    public function __construct()
    {
        $this->id = '';
        $this->internalId = '';
        $this->encryptedItemKeys = new ArrayCollection();
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
     * @return null|string
     */
    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    /**
     * @param string $internalId
     */
    public function setInternalId(string $internalId): void
    {
        $this->internalId = $internalId;
    }

    /**
     * @return Collection|null
     * @psalm-suppress LessSpecificReturnType
     */
    public function getEncryptedItemKeys(): ?Collection
    {
        return $this->encryptedItemKeys;
    }

    /**
     * @param KeyPair $keyPair
     * @return null|EncryptedItemKey
     */
    public function getEncryptedItemKeyForKeyPair(KeyPair $keyPair): ?EncryptedItemKey
    {
        $encryptedItemKeys = $this->getEncryptedItemKeys();

        if ($encryptedItemKeys) {
            /** @var EncryptedItemKey $encryptedItemKey */
            foreach ($encryptedItemKeys as $encryptedItemKey) {
                // In theory, there might be several EncryptedItemKeys for one EncryptableEntity. We only return the
                // first one.
                if ($encryptedItemKey->getKeyPair()->getId() === $keyPair->getId()) {
                    return $encryptedItemKey;
                }
            }
        }

        return null;
    }

    /**
     * @param EncryptedItemKey $encryptedItemKey
     * @return EncryptableEntity
     */
    public function addEncryptedItemKey(EncryptedItemKey $encryptedItemKey): EncryptableEntity
    {
        $this->encryptedItemKeys->add($encryptedItemKey);
        return $this;
    }

    /**
     * @param EncryptedItemKey $encryptedItemKey
     */
    public function removeEncryptedItemKey(EncryptedItemKey $encryptedItemKey): void
    {
        $this->encryptedItemKeys->removeElement($encryptedItemKey);
    }
}
