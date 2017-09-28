<?php

namespace SboxUserSpecificEntityEncryptionBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Sbox\MessageBundle\Entity\Message;
use Sbox\UserSpecificEntityEncryptionBundle\Service\EncryptedAnnotationHelper;

class EncryptedAnnotationHelperTest extends TestCase
{
    /** @var  EncryptedAnnotationHelper */
    protected $encryptedAnnotationHelper;

    /** @var  Message */
    protected $message;

    public function setUp()
    {
        $this->encryptedAnnotationHelper = new EncryptedAnnotationHelper(
            new AnnotationReader(),
            [
                ['key' => 'message', 'class' => 'Sbox\MessageBundle\Entity\Message'],
                ['key' => 'attachment', 'class' => 'Sbox\MessageBundle\Entity\Attachment'],
            ]
        );

        $this->message = new Message();
    }

    public function testGetEncryptedProperties()
    {
        $encryptedProperties = $this->encryptedAnnotationHelper->getEncryptedProperties($this->message);

        $this->assertNotEmpty($encryptedProperties);
        $this->assertGreaterThan(0, count($encryptedProperties));
    }
}
