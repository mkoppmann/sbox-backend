<?php

namespace Sbox\CryptoBundle\GenericHashing;

class GenericHash
{
    const MINIMUM_KEY_LENGTH = SODIUM_CRYPTO_GENERICHASH_KEYBYTES_MIN;
    const MAXIMUM_KEY_LENGTH = SODIUM_CRYPTO_GENERICHASH_KEYBYTES_MAX;

    /**
     * Calculates the hash of the given data with the length specified.
     * @param string $data
     * @param int $length
     * @return string
     */
    public static function hash(string $data, int $length = 32): string
    {
        return sodium_crypto_generichash($data, null, $length);
    }

    /**
     * Calculates the keyed hash of the given data with the length specified.
     * @param string $data
     * @param string $key
     * @param int $length
     * @return string
     */
    public static function keyedHash(string $data, string $key, int $length = 32): string
    {
        return sodium_crypto_generichash($data, $key, $length);
    }
}
