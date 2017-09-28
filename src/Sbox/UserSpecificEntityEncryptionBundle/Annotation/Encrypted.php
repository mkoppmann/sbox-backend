<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * This Doctrine annotation specifies whether or not a specific property of an entity shall be encrypted or not.
 * @package Sbox\UserSpecificEntityEncryptionBundle\Annotation
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Encrypted
{
}
