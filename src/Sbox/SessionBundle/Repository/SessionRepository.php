<?php

namespace Sbox\SessionBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class SessionRepository
 *
 * @package Sbox\SessionBundle\Repository
 * @author  Nikita Loges
 */
class SessionRepository extends EntityRepository implements SessionRepositoryInterface
{

    /**
     * @return bool
     */
    public function purge(): bool
    {
        $qb = $this->createQueryBuilder('r');
        $qb->delete();
        $qb->where($qb->expr()->lt('r.endOfLife', ':endOfLife'));
        $qb->setParameter('endOfLife', new \DateTime());

        return $qb->getQuery()->execute() > 0;
    }

    /**
     * @param string $sessionId
     * @return bool
     */
    public function destroy(string $sessionId): bool
    {
        $qb = $this->createQueryBuilder('r');
        $qb->delete();
        $qb->where($qb->expr()->eq('r.sessionId', ':session_id'));
        $qb->setParameter('session_id', $sessionId);

        return $qb->getQuery()->execute() > 0;
    }
}
