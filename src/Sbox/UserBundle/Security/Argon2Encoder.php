<?php

namespace Sbox\UserBundle\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class Argon2Encoder implements PasswordEncoderInterface
{

    /**
     * Encodes the raw password.
     *
     * @param string $raw The password to encode
     * @param string $salt The salt
     * @return string The encoded password
     */
    public function encodePassword($raw, $salt): string
    {
        $hash_str = sodium_crypto_pwhash_str(
            $raw,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );

        return $hash_str;
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw A raw password
     * @param string $salt The salt
     * @return bool true if the password is valid, false otherwise
     * @psalm-suppress TooFewArguments
     */
    public function isPasswordValid($encoded, $raw, $salt): bool
    {
        $result = sodium_crypto_pwhash_str_verify($encoded, $raw);
        // wipe the plaintext password from memory
        sodium_memzero($raw);
        return $result;
    }
}
