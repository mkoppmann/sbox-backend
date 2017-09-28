<?php

namespace SboxUserSpecificEntityEncryptionBundle\Subscriber\EntityRelation;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sbox\UserSpecificEntityEncryptionBundle\Subscriber\EntityMetadata\DynamicUserKeyPairRelationSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DynamicUserKeyPairRelationSubscriberTest extends KernelTestCase
{
    /** @var  string */
    protected $userFieldName;

    /** @var  string */
    protected $userClass;

    /** @var  string */
    protected $keyPairClass;

    /** @var DynamicUserKeyPairRelationSubscriber */
    protected $dynamicUserKeyPairRelationSubscriber;

    /** @var LoadClassMetadataEventArgs */
    protected $eventArgsMock;

    /** @var ClassMetadata */
    protected $classMetadata;

    protected function setUp(): void
    {
        $this->userClass = 'My\Bundle\Entity\User';
        $this->keyPairClass = 'Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPair';
        $this->userFieldName = 'user';

        $this->dynamicUserKeyPairRelationSubscriber = new DynamicUserKeyPairRelationSubscriber(
            $this->userClass,
            $this->keyPairClass
        );

        $this->classMetadata = new ClassMetadataInfo($this->keyPairClass);

        $this->eventArgsMock = $this->createMock(LoadClassMetadataEventArgs::class);
        $this->eventArgsMock->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->classMetadata);
    }

    /**
     * Tests if the subscriber returns the correct subscribed events.
     */
    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = $this->dynamicUserKeyPairRelationSubscriber->getSubscribedEvents();
        $this->assertEquals([Events::loadClassMetadata], $subscribedEvents);
    }

    /**
     * Tests if the key pair-user relationship is added correctly.
     */
    public function testLoadClassMetadata(): void
    {
        $this->dynamicUserKeyPairRelationSubscriber->loadClassMetadata($this->eventArgsMock);

        $this->assertArrayHasKey($this->userFieldName, $this->classMetadata->associationMappings);

        $this->assertEquals(
            $this->classMetadata->associationMappings[$this->userFieldName]['targetEntity'],
            $this->userClass
        );

        $this->assertEquals(
            $this->classMetadata->associationMappings[$this->userFieldName]['sourceEntity'],
            $this->keyPairClass
        );
    }
}
