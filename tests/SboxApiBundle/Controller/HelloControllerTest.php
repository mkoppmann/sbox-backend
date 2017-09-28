<?php

namespace Sbox\ApiBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class HelloControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $testPassword = 'TestP@ssw0rd!';

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
     * Tests the hello API point
     * @return void
     */
    public function testHello(): void
    {
        /** First, we authenticate the client. */
        $this->client->request(
            'POST',
            '/api/user/login',
            array('username' => 'testuser', 'password' => $this->testPassword)
        );

        $this->client->request('GET', '/api/hello');
        $this->assertContains('world', $this->client->getResponse()->getContent());
    }
}
