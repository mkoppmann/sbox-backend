<?php

namespace SboxCryptoBundle\KeyDerivation;

use PHPUnit\Framework\TestCase;
use Sbox\CryptoBundle\KeyDerivation\ExpensiveKeyDerivation;
use Sbox\CryptoBundle\Randomness\RandomGenerator;

class ExpensiveKeyDerivationTest extends TestCase
{
    /** @var  string */
    protected $password;

    /** @var  int */
    protected $keyLength;

    /** @var  string */
    protected $salt;

    public function setUp()
    {
        $this->password = 'TestP@ssw0rd!';
        $this->salt = RandomGenerator::generateRandomBytes(ExpensiveKeyDerivation::SALT_LENGTH);
        $this->keyLength = 96;
    }

    public function testDeriveKeyFromPassword()
    {
        $derivedKey = ExpensiveKeyDerivation::deriveKeyFromPassword($this->keyLength, $this->password, $this->salt);
        $derivedKeyLength = strlen($derivedKey);

        $this->assertNotEmpty($derivedKey);
        $this->assertEquals($this->keyLength, $derivedKeyLength);
    }
}
