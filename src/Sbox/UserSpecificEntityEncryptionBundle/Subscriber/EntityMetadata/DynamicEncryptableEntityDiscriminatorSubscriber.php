<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\EntityMetadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptableEntity;

class DynamicEncryptableEntityDiscriminatorSubscriber implements EventSubscriber
{
    /** @var array */
    protected $encryptableEntities;

    /**
     * DynamicEncryptableEntityDiscriminatorSubscriber constructor.
     * @param array $encryptableEntities
     */
    public function __construct(array $encryptableEntities)
    {
        $this->encryptableEntities = $encryptableEntities;
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

        if ($metadata->getName() === EncryptableEntity::class) {
            // Now we dynamically add to the discriminator map the entities that have been configured as encryptable.
            foreach ($this->encryptableEntities as $encryptableEntity) {
                $metadata->addDiscriminatorMapClass(
                    $encryptableEntity['key'],
                    $encryptableEntity['class']
                );
            }
        }
    }
}
