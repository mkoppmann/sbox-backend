<?php

namespace Sbox\CryptoBundle\SecretKeyCryptography;

use Sbox\CryptoBundle\Randomness\RandomGenerator;

class AuthenticatedSecretKeyCryptography
{
    const NONCE_LENGTH = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    const KEY_LENGTH = SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
    const SECRET_LENGTH = self::NONCE_LENGTH + self::KEY_LENGTH;

    /** @var  string */
    protected $nonce;

    /** @var  string */
    protected $key;

    /**
     * SecretKeyCryptography constructor.
     * @param string $nonce
     * @param string $key
     */
    public function __construct(string $nonce, string $key)
    {
        $this->nonce = $nonce;
        $this->key = $key;
    }

    /**
     * Constructs a new SecretKeyCryptography object using a secret instead of a nonce and a key. A secret is simply the
     * nonce and the key combined into one single array of bytes.
     * @param string $secret
     * @return AuthenticatedSecretKeyCryptography
     */
    public static function constructWithSecret(string $secret): AuthenticatedSecretKeyCryptography
    {
        if (strlen($secret) !== (self::NONCE_LENGTH + self::KEY_LENGTH)) {
            throw new \InvalidArgumentException(
                'The secret length must equal the total of the nonce length and the key length.'
            );
        }

        $nonce = substr($secret, 0, self::NONCE_LENGTH);
        $key =   substr($secret, self::NONCE_LENGTH);

        return new self($nonce, $key);
    }

    /**
     * Encrypts the given plaintext with the previously set nonce and key.
     * @param string $plaintext
     * @return string
     */
    public function encrypt(string $plaintext): string
    {
        return sodium_crypto_secretbox($plaintext, $this->nonce, $this->key);
    }

    public function decrypt(string $ciphertext): string
    {
        return sodium_crypto_secretbox_open($ciphertext, $this->nonce, $this->key);
    }

    /**
     * Generates a random nonce with the correct length.
     * @return string
     */
    public static function generateRandomNonce(): string
    {
        return RandomGenerator::generateRandomBytes(self::NONCE_LENGTH);
    }

    /**
     * Gnerates a random key with the correct length.
     * @return string
     */
    public static function generateRandomKey(): string
    {
        return RandomGenerator::generateRandomBytes(self::KEY_LENGTH);
    }

    /**
     * Generates a random secret with the correct length (nonce length + key length).
     * @return string
     */
    public static function generateRandomSecret(): string
    {
        return RandomGenerator::generateRandomBytes(self::NONCE_LENGTH + self::KEY_LENGTH);
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     */
    public function setNonce(string $nonce): void
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }
}
