<?php

namespace Sbox\UserBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Sbox\UserBundle\Entity\User;
use Sbox\UserBundle\Manager\UserManager;
use Sbox\UserBundle\Manager\UserManagerInterface;
use Sbox\UserBundle\Security\UserAuthenticator;
use Sbox\UserBundle\Security\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserAuthenticatorTest extends KernelTestCase
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserAuthenticator
     */
    private $userAuthenticator;

    /**
     * @var UserInterface
     */
    private $testUser;

    /**
     * @var string
     */
    private $testPassword = 'TestP@ssw0rd!';

    /**
     * @var array
     */
    private $usernamePasswordArray = array();

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
            ->willReturn('encodedPassword');

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
        $this->userAuthenticator = new UserAuthenticator($this->userProvider, $encoderFactoryMock);

        $this->testUser = new User();
        $this->testUser->setUsername('testuser');
        $this->testUser->setPassword($this->testPassword);

        $this->userRepositoryMock->expects($this->any())
            ->method('findOneBy')
            ->willReturn($this->testUser);

        $this->usernamePasswordArray = array(
            'username' => $this->testUser->getUsername(),
            'password' => $this->testPassword
        );

        $passwordEncoderMock->expects($this->any())
            ->method('isPasswordValid')
            ->will(
                $this->returnValueMap([
                    [$this->testPassword, 'Wrong', '', false],
                    [$this->testPassword, $this->testPassword, '', true]
                ])
            );
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
        $this->userAuthenticator = null;
        $this->testUser = null;
        $this->usernamePasswordArray = null;
    }

    /**
     * Tests if the returned response directs the user to authenticate
     * @return void
     */
    public function testStart(): void
    {
        $getRequest = Request::create('/api/hello', 'GET');
        $response = $this->userAuthenticator->start($getRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Tests if a credential array gets returned with a given login request
     * @return void
     */
    public function testGetCredentials(): void
    {
        $testRequest = Request::create(
            '/api/user/login',
            'POST',
            $this->usernamePasswordArray
        );

        $returnValue = $this->userAuthenticator->getCredentials($testRequest);

        $this->assertEquals($this->usernamePasswordArray, $returnValue);
    }

    /**
     * Tests if the correct user gets returned with a given credential array
     * @return void
     */
    public function testGetUser(): void
    {
        $returnValue = $this->userAuthenticator->getUser($this->usernamePasswordArray, $this->userProvider);

        $this->assertInstanceOf(UserInterface::class, $returnValue);
        $this->assertEquals($this->usernamePasswordArray['username'], $returnValue->getUsername());
    }

    /**
     * Tests login with wrong user
     * @return void
     */
    public function testCheckCredentialsWrongUser(): void
    {
        $wrongUsernamePasswordArray = array(
            'username' => "Bob",
            'password' => "Wrong"
        );
        $wrongUser = new User();

        try {
            $this->userAuthenticator->checkCredentials($wrongUsernamePasswordArray, $wrongUser);
        } catch (AuthenticationException $e) {
            $this->assertInstanceOf(AuthenticationException::class, $e);
        }
    }

    /**
     * Tests login with right user but wrong password
     * @return void
     */
    public function testCheckCredentialsRightUserWrongPassword(): void
    {
        $wrongUsernamePasswordArray = array(
            'username' => $this->testUser->getUsername(),
            'password' => "Wrong"
        );

        try {
            $this->userAuthenticator->checkCredentials($wrongUsernamePasswordArray, $this->testUser);
        } catch (AuthenticationException $e) {
            $this->assertInstanceOf(AuthenticationException::class, $e);
        }
    }

    /**
     * Tests login with right user and correct password
     * @return void
     */
    public function testCheckCredentialsRightUserRightPassword(): void
    {
        $this->assertTrue($this->userAuthenticator->checkCredentials($this->usernamePasswordArray, $this->testUser));
    }

    /**
     * Tests the failed authentication response
     * @return void
     */
    public function testOnAuthenticationFailure(): void
    {
        $testRequest = Request::create(
            '/api/user/login',
            'POST',
            $this->usernamePasswordArray
        );

        /** @var JsonResponse $returnValue */
        $returnValue = $this->userAuthenticator->onAuthenticationFailure(
            $testRequest,
            new AuthenticationException($this->userAuthenticator::AUTHENTICATION_UNSUCCESSFUL_MESSAGE)
        );

        $this->assertInstanceOf(JsonResponse::class, $returnValue);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $returnValue->getStatusCode());
    }

    /**
     * Tests the successful authentication response
     */
    public function testOnAuthenticationSuccess(): void
    {
        $testRequest = Request::create(
            '/api/user/login',
            'POST',
            $this->usernamePasswordArray
        );

        $usernamePasswordToken = new UsernamePasswordToken(
            $this->testUser,
            $this->testPassword,
            'sbox_user.user_provider'
        );

        /** @var JsonResponse $returnValue */
        $returnValue = $this->userAuthenticator->onAuthenticationSuccess(
            $testRequest,
            $usernamePasswordToken,
            null
        );

        $this->assertInstanceOf(JsonResponse::class, $returnValue);
        $this->assertEquals(Response::HTTP_OK, $returnValue->getStatusCode());
    }
}
