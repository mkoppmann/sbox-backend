<?php

namespace SboxCryptoBundle\PublicKeyCryptography;

use PHPUnit\Framework\TestCase;
use Sbox\CryptoBundle\PublicKeyCryptography\PublicKeyCryptography;

class PublicKeyCryptographyTest extends TestCase
{
    /** @var  PublicKeyCryptography */
    protected $publicKeyCryptography;

    /** @var  string */
    protected $keyPair;

    /** @var  int */
    protected $keyPairLength;

    /** @var  string */
    protected $plaintext;

    public function setUp()
    {
        $this->publicKeyCryptography = new PublicKeyCryptography();
        $this->keyPair = $this->publicKeyCryptography::generateKeyPair();
        $this->publicKeyCryptography->setKeyPair($this->keyPair);
        $this->keyPairLength = strlen($this->keyPair);
        $this->plaintext = 'This is the secret message that has to be encrypted securely.';
    }

    public function testEncryptDecrypt()
    {
        $encryptedMessage = $this->publicKeyCryptography->encrypt($this->plaintext);
        $decryptedMessage = $this->publicKeyCryptography->decrypt($encryptedMessage);

        $this->assertEquals($this->plaintext, $decryptedMessage);
    }

    public function testGenerateKeyPair()
    {
        $keyPair = $this->publicKeyCryptography::generateKeyPair();
        $keyPairLength = strlen($keyPair);

        $this->assertGreaterThan(0, $keyPairLength);
    }

    public function testGetPublicKeyFromKeyPair()
    {
        $publicKey = $this->publicKeyCryptography::getPublicKeyFromKeyPair($this->keyPair);
        $publicKeyLength = strlen($publicKey);

        $this->assertGreaterThan(0, $publicKeyLength);
        $this->assertLessThan($this->keyPairLength, $publicKeyLength);
    }

    public function testGetPublicKeyFromSecretKey()
    {
        $publicKey = $this->publicKeyCryptography::getPublicKeyFromSecretKey(
            $this->publicKeyCryptography->getSecretKey()
        );

        $this->assertEquals($this->publicKeyCryptography->getPublicKey(), $publicKey);
    }

    public function testGetSecretKeyFromKeyPair()
    {
        $secretKey = $this->publicKeyCryptography::getSecretKeyFromKeyPair($this->keyPair);
        $secretKeyLength = strlen($secretKey);

        $this->assertGreaterThan(0, $secretKeyLength);
        $this->assertLessThan($this->keyPairLength, $secretKeyLength);
    }

    public function testGetKeyPairFromPublicKeyAndSecretKey()
    {
        $publicKey = $this->publicKeyCryptography::getPublicKeyFromKeyPair($this->keyPair);
        $secretKey = $this->publicKeyCryptography::getSecretKeyFromKeyPair($this->keyPair);

        $keyPair = $this->publicKeyCryptography::getKeyPairFromPublicKeyAndSecretKey($publicKey, $secretKey);

        $this->assertEquals($this->keyPair, $keyPair);
    }
}
