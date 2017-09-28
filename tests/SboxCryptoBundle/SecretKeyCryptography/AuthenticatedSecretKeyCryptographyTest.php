<?php

namespace SboxCryptoBundle\SecretKeyCryptography;

use PHPUnit\Framework\TestCase;
use Sbox\CryptoBundle\SecretKeyCryptography\AuthenticatedSecretKeyCryptography;

class AuthenticatedSecretKeyCryptographyTest extends TestCase
{
    /** @var  AuthenticatedSecretKeyCryptography */
    protected $authenticatedSecretKeyCryptography;

    /** @var  string */
    protected $secret;

    /** @var  string */
    protected $plaintext;

    public function setUp()
    {
        $this->authenticatedSecretKeyCryptography = AuthenticatedSecretKeyCryptography::constructWithSecret(
            AuthenticatedSecretKeyCryptography::generateRandomSecret()
        );

        $this->secret = AuthenticatedSecretKeyCryptography::generateRandomSecret();

        $this->plaintext = 'This is the secret message that has to be encrypted securely.';
    }

    public function testConstructWithSecret()
    {
        $secretKeyCryptography = AuthenticatedSecretKeyCryptography::constructWithSecret($this->secret);

        $this->assertNotNull($secretKeyCryptography);
        $this->assertNotNull($secretKeyCryptography->getNonce());
        $this->assertNotNull($secretKeyCryptography->getKey());
        $this->assertEquals(
            AuthenticatedSecretKeyCryptography::NONCE_LENGTH + AuthenticatedSecretKeyCryptography::KEY_LENGTH,
            strlen($secretKeyCryptography->getNonce()) + strlen($secretKeyCryptography->getKey())
        );
        $this->assertEquals(
            AuthenticatedSecretKeyCryptography::NONCE_LENGTH,
            strlen($secretKeyCryptography->getNonce())
        );
        $this->assertEquals(
            AuthenticatedSecretKeyCryptography::KEY_LENGTH,
            strlen($secretKeyCryptography->getKey())
        );
    }

    public function testEncryptDecrypt()
    {
        $ciphertext = $this->authenticatedSecretKeyCryptography->encrypt($this->plaintext);
        $plaintext = $this->authenticatedSecretKeyCryptography->decrypt($ciphertext);

        $this->assertNotEmpty($ciphertext);
        $this->assertNotEmpty($plaintext);
        $this->assertEquals($this->plaintext, $plaintext);
        $this->assertNotEquals($this->plaintext, $ciphertext);
    }

    public function testAuthenticatedEncryptionAndDecryption()
    {
        $ciphertext = $this->authenticatedSecretKeyCryptography->encrypt($this->plaintext);

        // Manipulating the ciphertext by XOR'ing the 35th character of the ciphertext with the ASCII character with the
        // decimal value of 217.
        $ciphertext[35] = chr(ord($ciphertext[35]) ^ 217);

        $plaintext = $this->authenticatedSecretKeyCryptography->decrypt($ciphertext);

        // The authenticated decryption has failed if the $plaintext is empty.
        $this->assertEmpty($plaintext);
    }

    public function testGenerateRandomNonce()
    {
        $randomNonce = AuthenticatedSecretKeyCryptography::generateRandomNonce();

        $this->assertNotNull($randomNonce);
        $this->assertNotEmpty($randomNonce);
        $this->assertEquals(AuthenticatedSecretKeyCryptography::NONCE_LENGTH, strlen($randomNonce));
    }

    public function testGenerateRandomKey()
    {
        $randomKey = AuthenticatedSecretKeyCryptography::generateRandomKey();

        $this->assertNotNull($randomKey);
        $this->assertNotEmpty($randomKey);
        $this->assertEquals(AuthenticatedSecretKeyCryptography::KEY_LENGTH, strlen($randomKey));
    }
}
