<?php

namespace SboxApiBundle\Security;

use PHPUnit\Framework\TestCase;
use Sbox\UserBundle\Security\Argon2Encoder;

class Argon2EncoderTest extends TestCase
{
    /** @var  Argon2Encoder */
    private $passEncoder;

    /** @var string */
    private $testPassword1 = "Testpassword";

    /** @var null */
    private $testSalt = null;

    /** @var string */
    private $expectedHashType = "\$argon2i$";

    protected function setUp()
    {
        $this->passEncoder = new Argon2Encoder();
    }

    public function testEncodePasswordStartsWithArgon2i()
    {
        $hash_str = $this->passEncoder->encodePassword($this->testPassword1, $this->testSalt);
        $this->assertStringStartsWith($this->expectedHashType, $hash_str);
    }

    public function testEncodePasswordTwoSamePasswordsShouldBeDifferentHash()
    {
        $hash_str1 = $this->passEncoder->encodePassword($this->testPassword1, $this->testSalt);
        $hash_str2 = $this->passEncoder->encodePassword($this->testPassword1, $this->testSalt);
        $this->assertNotEquals($hash_str1, $hash_str2);
    }

    public function testIfIsPasswordValidWorks()
    {
        $hash_str = $this->passEncoder->encodePassword($this->testPassword1, $this->testSalt);
        $this->assertTrue($this->passEncoder->isPasswordValid($hash_str, $this->testPassword1, $this->testSalt));
    }
}
