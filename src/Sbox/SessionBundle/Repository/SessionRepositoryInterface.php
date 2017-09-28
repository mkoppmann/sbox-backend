<?php

namespace Sbox\SessionBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Interface SessionRepositoryInterface
 *
 * @package Sbox\SessionBundle\Repository
 * @author  Nikita Loges
 */
interface SessionRepositoryInterface extends ObjectRepository
{

    /**
     * @return bool
     */
    public function purge(): bool;

    /**
     * @param string $sessionId
     * @return bool
     */
    public function destroy(string $sessionId): bool;
}
