<?php

namespace Sbox\CryptoBundle\Randomness;

class RandomGenerator
{
    /**
     * Generates $numBytes random bytes.
     * @param int $numBytes
     * @return string
     */
    public static function generateRandomBytes(int $numBytes): string
    {
        return random_bytes($numBytes);
    }
}
