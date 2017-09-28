<?php

namespace SboxCryptoBundle\Randomness;

use PHPUnit\Framework\TestCase;
use Sbox\CryptoBundle\Randomness\RandomGenerator;

class RandomGeneratorTest extends TestCase
{
    /** @var  int */
    protected $numBytes;

    public function setUp()
    {
        $this->numBytes = 32;
    }

    public function testGenerateRandomBytes()
    {
        $randomBytes = RandomGenerator::generateRandomBytes($this->numBytes);
        $randomBytesLength = strlen($randomBytes);

        $this->assertEquals($this->numBytes, $randomBytesLength);
    }
}
