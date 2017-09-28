<?php

namespace Sbox\CryptoBundle\PublicKeyCryptography;

class PublicKeyCryptography
{
    const NONCE_LENGTH = SODIUM_CRYPTO_BOX_NONCEBYTES;

    /** @var  string */
    protected $nonce;

    /** @var  string */
    protected $publicKey;

    /** @var  string */
    protected $secretKey;

    public function __construct()
    {
        $this->nonce = '';
        $this->publicKey = '';
        $this->secretKey = '';
    }

    /**
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        return sodium_crypto_box_seal($data, $this->publicKey);
    }

    /**
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $keyPair = self::getKeyPairFromPublicKeyAndSecretKey(
            self::getPublicKeyFromSecretKey($this->secretKey),
            $this->secretKey
        );

        return sodium_crypto_box_seal_open($data, $keyPair);
    }

    /**
     * Takes a key pair, extracts the public and the secret key from it and sets the corresponding properties.
     * @param string $keyPair
     */
    public function setKeyPair(string $keyPair): void
    {
        $this->publicKey = self::getPublicKeyFromKeyPair($keyPair);
        $this->secretKey = self::getSecretKeyFromKeyPair($keyPair);
    }

    /**
     * Randomly generates a new key pair. Note that the secret key is returned in clear-text.
     * @return string
     */
    public static function generateKeyPair(): string
    {
        $keyPair = sodium_crypto_box_keypair();
        return $keyPair;
    }

    /**
     * Extracts the public key from the key pair and returns it.
     * @param string $keyPair
     * @return string
     */
    public static function getPublicKeyFromKeyPair(string $keyPair): string
    {
        return sodium_crypto_box_publickey($keyPair);
    }

    /**
     * @param string $secretKey
     * @return string
     */
    public static function getPublicKeyFromSecretKey(string $secretKey): string
    {
        return sodium_crypto_box_publickey_from_secretkey($secretKey);
    }

    /**
     * Extracts the secret key from the key pair and returns it. Note that the secret key is returned in clear-text.
     * @param string $keyPair
     * @return string
     */
    public static function getSecretKeyFromKeyPair(string $keyPair): string
    {
        return sodium_crypto_box_secretkey($keyPair);
    }

    public static function getKeyPairFromPublicKeyAndSecretKey(string $publicKey, string $secretKey): string
    {
        return sodium_crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);
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
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }
}
