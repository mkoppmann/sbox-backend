<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Subscriber\EntityMetadata;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sbox\UserSpecificEntityEncryptionBundle\Service\EncryptedAnnotationHelper;

class EncryptedPropertyTypeSubscriber implements EventSubscriber
{
    /** @var  EncryptedAnnotationHelper */
    protected $encryptedAnnotationHelper;

    /** @var array */
    protected $encryptableEntities;

    /**
     * EncryptedPropertyTypeSubscriber constructor.
     * @param EncryptedAnnotationHelper $encryptedAnnotationHelper
     * @param array $encryptableEntities
     */
    public function __construct(EncryptedAnnotationHelper $encryptedAnnotationHelper, array $encryptableEntities)
    {
        $this->encryptedAnnotationHelper = $encryptedAnnotationHelper;
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

        if ($this->encryptedAnnotationHelper->isClassEligibleForEncryption($metadata->getName())) {
            $encryptedProperties = $this->encryptedAnnotationHelper->getEncryptedPropertiesFromReflectionProperties(
                $metadata->getReflectionClass()->getProperties()
            );

            /** @var \ReflectionProperty $encryptedProperty */
            foreach ($encryptedProperties as $encryptedProperty) {
                // Delete the old mapping.
                $fieldName = $encryptedProperty->getName();
                $columnName = $metadata->columnNames[$fieldName];
                unset($metadata->fieldMappings[$fieldName]);
                unset($metadata->columnNames[$fieldName]);
                unset($metadata->fieldNames[$columnName]);

                // Re-map the field with the custom type "binarystring".
                $metadata->mapField(array(
                    'fieldName' => $encryptedProperty->getName(),
                    'type' => 'binarystring'
                ));
            }
        }
    }
}
