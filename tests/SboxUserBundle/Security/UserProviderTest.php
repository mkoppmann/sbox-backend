<?php

namespace Sbox\UserBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Sbox\UserBundle\Entity\User;
use Sbox\UserBundle\Manager\UserManager;
use Sbox\UserBundle\Manager\UserManagerInterface;
use Sbox\UserBundle\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProviderTest extends KernelTestCase
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var UserInterface
     */
    private $testUser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    /**
     * setUp function
     * @return void
     */
    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(ObjectRepository::class);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->userRepositoryMock);

        $passwordEncoderMock = $this->createMock(PasswordEncoderInterface::class);
        $passwordEncoderMock->expects($this->any())
            ->method('encodePassword')
            ->willReturn('encodePassword');

        $encoderFactoryMock = $this->createMock(EncoderFactory::class);
        $encoderFactoryMock->expects($this->any())
            ->method('getEncoder')
            ->willReturn($passwordEncoderMock);

        /**
         * @var EntityManager $entityManagerMock
         * @var EncoderFactory $encoderFactoryMock
         */
        $this->userManager = new UserManager($entityManagerMock, $encoderFactoryMock);
        $this->userProvider = new UserProvider($this->userManager);

        $this->testUser = new User();
        $this->testUser->setUsername('testuser');
    }

    /**
     * tearDown function
     * @return void
     */
    protected function tearDown(): void
    {
        $this->userRepositoryMock = null;
        $this->userManager = null;
        $this->userProvider = null;
        $this->testUser = null;
    }

    /**
     * Tests the loading of a user from the database by given username
     * @return void
     */
    public function testLoadUserByUsername(): void
    {
        $this->userRepositoryMock->expects($this->any())
            ->method('findOneBy')
            ->willReturn($this->testUser);

        $user = $this->userProvider->loadUserByUsername($this->testUser->getUsername());

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->testUser->getUsername(), $user->getUsername());
    }

    /**
     * Tests the reloading of the given user
     * @return void
     */
    public function testRefreshUser(): void
    {
        $this->userRepositoryMock->expects($this->any())
            ->method('findOneBy')
            ->willReturn($this->testUser);

        $user = $this->userProvider->refreshUser($this->testUser);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->testUser->getUsername(), $user->getUsername());
    }

    /**
     * Tests if the provider supports the user class
     * @return void
     */
    public function testSupportsClass(): void
    {
        $supportsClass = $this->userProvider->supportsClass(User::class);

        $this->assertTrue($supportsClass);
    }
}
