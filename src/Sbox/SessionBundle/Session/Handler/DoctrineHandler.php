<?php

namespace Sbox\SessionBundle\Session\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Sbox\SessionBundle\Entity\Session;
use Sbox\SessionBundle\Repository\SessionRepositoryInterface;

/**
 * Class DoctrineHandler
 *
 * @package Sbox\SessionBundle\Session\Handler
 * @author  Nikita Loges
 */
class DoctrineHandler implements \SessionHandlerInterface
{

    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->entityManager->flush();
        return true;
    }

    /**
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id): bool
    {
        return $this->getRepository()->destroy($session_id);
    }

    /**
     * @return SessionRepositoryInterface
     */
    protected function getRepository(): SessionRepositoryInterface
    {
        /**
         * @var SessionRepositoryInterface $repository
         */
        $repository = $this->entityManager->getRepository(Session::class);
        return $repository;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        $this->getRepository()->purge();
        return true;
    }

    /**
     * @param string $save_path
     * @param string $session_id
     * @return bool
     */
    public function open($save_path, $session_id): bool
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return string
     */
    public function read($session_id): string
    {
        $session = $this->getSession($session_id);

        if (!$session || is_null($session->getSessionData())) {
            return '';
        }

        $sessionData = $session->getSessionData();

        if (isset($sessionData)) {
            return is_resource($sessionData) ? stream_get_contents($sessionData) : $sessionData;
        } else {
            return '';
        }
        // return is_resource($resource) ? stream_get_contents($resource) : $resource;
    }

    /**
     * @param $session_id
     * @return Session
     */
    protected function getSession($session_id): Session
    {
        $session = $this->getRepository()->findOneBy(['sessionId' => $session_id]);

        if (!$session) {
            $session = $this->newSession($session_id);
        }

        return $session;
    }

    /**
     * @param $session_id
     * @return Session
     */
    protected function newSession($session_id): Session
    {
        $className = $this->getRepository()->getClassName();

        /**
         * @var Session $session
         */
        $session = new $className;
        $session->setSessionId($session_id);

        return $session;
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data): bool
    {
        /**
         * @var int $maxlifetime
         */
        $maxlifetime = (int)ini_get('session.gc_maxlifetime');

        /**
         * @var resource $sessionData
         */
        $sessionData = $session_data;

        $now = new \DateTime();
        $endOfLife = new \DateTime();
        $endOfLife->add(new \DateInterval('PT' . $maxlifetime . 'S'));

        $session = $this->getSession($session_id);
        $session->setSessionData($sessionData);
        $session->setUpdatedAt($now);
        $session->setEndOfLife($endOfLife);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return true;
    }
}
