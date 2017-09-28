<?php

namespace Sbox\CryptoBundle\KeyDerivation;

class ExpensiveKeyDerivation
{
    const SALT_LENGTH = SODIUM_CRYPTO_PWHASH_SALTBYTES;

    /**
     * @param int $keyLength
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function deriveKeyFromPassword(int $keyLength, string $password, string $salt): string
    {
        return sodium_crypto_pwhash(
            $keyLength,
            $password,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );
    }
}
