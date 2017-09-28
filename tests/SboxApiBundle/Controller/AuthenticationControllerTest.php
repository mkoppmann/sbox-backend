<?php

namespace Sbox\ApiBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class AuthenticationControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $testPassword = "TestP@ssw0rd!";

    /**
     * setUp function
     * @return void
     */
    protected function setUp(): void
    {
        $this->loadFixtures([
            'Sbox\UserBundle\DataFixtures\ORM\LoadUserData',
        ]);

        $this->client = static::createClient();
    }

    /**
     * tearDown function
     * @return void
     */
    protected function tearDown(): void
    {
        $this->client = null;
    }

    /**
     * Tests if the user needs to be authenticated
     * @return void
     */
    public function testAuthRequired(): void
    {
        $this->client->request('POST', '/api/user/login');
        $this->assertContains('Authentication required', $this->client->getResponse()->getContent());
    }

    /**
     * Tests login with wrong credentials
     * @return void
     */
    public function testAuthException(): void
    {
        $this->client->request('POST', '/api/user/login', array('username' => ''));
        $this->assertContains('Invalid credentials.', $this->client->getResponse()->getContent());
    }

    /**
     * Tests a valid login attempt
     * @return void
     */
    public function testLogin(): void
    {
        $this->client->request(
            'POST',
            '/api/user/login',
            array('username' => 'testuser', 'password' => $this->testPassword)
        );

        $this->assertContains('Authentication successful.', $this->client->getResponse()->getContent());
    }
}
