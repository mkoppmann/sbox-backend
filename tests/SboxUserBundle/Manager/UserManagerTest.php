<?php

namespace Sbox\UserBundle\Tests\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Sbox\UserBundle\Entity\User;
use Sbox\UserBundle\Manager\UserManager;
use Sbox\UserBundle\Manager\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManagerTest extends KernelTestCase
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var string
     */
    private $testUsername = 'testuser';

    /**
     * @var string
     */
    private $testPassword = 'TestP@ssw0rd!';

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

        $this->userManager = new UserManager($entityManagerMock, $encoderFactoryMock);
    }

    /**
     * tearDown function
     * @return void
     */
    protected function tearDown(): void
    {
        $this->userRepositoryMock = null;
        $this->userManager = null;
    }

    /**
     * Test to find a user by id
     */
    public function testFindUserBy(): void
    {
        $testUser = new User();
        $testUser->setUsername($this->testUsername);

        $this->userRepositoryMock->expects($this->any())
            ->method('findOneBy')
            ->willReturn($testUser);

        $user = $this->userManager->findUserBy(array('username' => $this->testUsername));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->testUsername, $user->getUsername());
    }

    /**
     * Test fo find all users
     */
    public function testGetUsers(): void
    {

        $this->userRepositoryMock->expects($this->any())
            ->method('findAll')
            ->willReturn(array());

        $users = $this->userManager->getUsers();

        //var_dump($users);

        $this->assertInternalType('array', $users);
    }

    /**
     * Test to create a user
     */
    public function testCreateUser(): void
    {
        $username = $this->testUsername;
        $password = $this->testPassword;
        $email = "test@example.com";
        $name = "Test User";
        $superadmin = false;

        /** @var UserManagerInterface $userManager */
        $user = $this->userManager->createUser($username, $password, $email, $name, $superadmin);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($username, $user->getUsername());
    }
}
