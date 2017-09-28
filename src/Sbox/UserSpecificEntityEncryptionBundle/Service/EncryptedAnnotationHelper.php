<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Sbox\UserSpecificEntityEncryptionBundle\Annotation\Encrypted;

class EncryptedAnnotationHelper
{
    /** @var Reader */
    protected $annotationReader;

    /** @var array */
    protected $encryptableEntities;

    public function __construct(Reader $annotationReader, array $encryptableEntities)
    {
        $this->annotationReader = $annotationReader;
        $this->encryptableEntities = $encryptableEntities;
    }

    /**
     * Returns all properties of an object with an \@Encrypted annotation.
     * @param $object
     * @return array
     */
    public function getEncryptedProperties($object)
    {
        $reflectionProperties = $this->getReflectionProperties($object);
        return $this->getEncryptedPropertiesFromReflectionProperties($reflectionProperties);
    }

    public function getEncryptedPropertiesFromReflectionProperties(array $reflectionProperties): array
    {
        $encryptedProperties = array();

        // Step through all properties and look if there is an @Encrypted annotation.
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($this->getEncryptedAnnotation($reflectionProperty)) {
                $encryptedProperties[] = $reflectionProperty;
            }
        }

        return $encryptedProperties;
    }

    /**
     * Returns whether or not the object uses encryption.
     * @param $object
     * @return bool
     */
    public function isObjectEligibleForEncryption($object): bool
    {
        return $this->isClassEligibleForEncryption(get_class($object));
    }

    /**
     * Returns whether or not the class uses encryption.
     * @param string $class
     * @return bool
     */
    public function isClassEligibleForEncryption(string $class): bool
    {
        foreach ($this->encryptableEntities as $entity) {
            if ($entity['class'] === $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the given property has an \@Encrypted annotation.
     * @param \ReflectionProperty $reflectionProperty
     * @return null|object
     */
    protected function getEncryptedAnnotation(\ReflectionProperty $reflectionProperty)
    {
        return $this->annotationReader->getPropertyAnnotation($reflectionProperty, Encrypted::class);
    }

    /**
     * Returns the reflection properties of a given object.
     * @param $object
     * @return \ReflectionProperty[]
     * @psalm-suppress MoreSpecificReturnType
     */
    protected function getReflectionProperties($object)
    {
        $reflectionClass = new \ReflectionClass($object);
        return $reflectionClass->getProperties();
    }
}
