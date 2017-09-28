<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\EntityMetadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

class DynamicUserKeyPairRelationSubscriber implements EventSubscriber
{
    /** @var string */
    protected $userClass;

    /** @var string */
    protected $keyPairClass;

    /**
     * DynamicUserKeyPairRelationSubscriber constructor.
     * @param $userClass
     * @param $keyPairClass
     */
    public function __construct(string $userClass, string $keyPairClass)
    {
        $this->userClass = $userClass;
        $this->keyPairClass = $keyPairClass;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::loadClassMetadata);
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        // The $metadata is the whole entity mapping info.
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata->getName() === $this->keyPairClass) {
            // We add the one-to-one relationship from the parameter to the user
            $metadata->mapOneToOne(array(
                'fieldName' => 'user',
                'targetEntity' => $this->userClass,
                'inversedBy' => 'keyPair',
                'joinColumns'   => array(array(
                    'name' => 'user_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE'
                ))
            ));
        }

        if ($metadata->getName() === $this->userClass) {
            $metadata->mapOneToOne(array(
                'fieldName' => 'keyPair',
                'targetEntity' => $this->keyPairClass,
                'mappedBy' => 'user',
                'cascade' => array(
                    'persist',
                    'remove'
                )
            ));
        }
    }
}
