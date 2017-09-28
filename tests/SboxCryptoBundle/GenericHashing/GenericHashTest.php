<?php

namespace SboxCryptoBundle\GenericHashing;

use PHPUnit\Framework\TestCase;
use Sbox\CryptoBundle\GenericHashing\GenericHash;

class GenericHashTest extends TestCase
{
    /** @var  string */
    protected $plainText;

    /** @var  string */
    protected $key;

    public function setUp()
    {
        $this->plainText = 'This is the text to be hashed.';
        $this->key = 'TestP@ssw0rd123!';
    }

    public function testHash()
    {
        $hash32 = GenericHash::hash($this->plainText, 32);
        $hash64 = GenericHash::hash($this->plainText, 64);

        $this->assertEquals(32, strlen($hash32));
        $this->assertEquals(64, strlen($hash64));
    }

    public function testKeyedHash()
    {
        $hash32 = GenericHash::keyedHash($this->plainText, $this->key, 32);
        $hash64 = GenericHash::keyedHash($this->plainText, $this->key, 64);

        $this->assertEquals(32, strlen($hash32));
        $this->assertEquals(64, strlen($hash64));
    }
}
