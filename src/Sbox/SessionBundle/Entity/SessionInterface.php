<?php

namespace Sbox\SessionBundle\Entity;

/**
 * Interface SessionInterface
 *
 * @package Sbox\SessionBundle\Entity
 * @author  Nikita Loges
 */
interface SessionInterface
{

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void;

    /**
     * @return string
     */
    public function getSessionId(): string;

    /**
     * @param string $sessionId
     * @return void
     */
    public function setSessionId(string $sessionId): void;

    /**
     * @return resource|null
     */
    public function getSessionData();

    /**
     * @param resource|null $sessionData
     * @return void
     */
    public function setSessionData($sessionData): void;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @param \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt): void;

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime;

    /**
     * @param \DateTime $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTime $updatedAt): void;

    /**
     * @return \DateTime
     */
    public function getEndOfLife(): \DateTime;

    /**
     * @param \DateTime $endOfLife
     * @return void
     */
    public function setEndOfLife(\DateTime $endOfLife): void;
}
