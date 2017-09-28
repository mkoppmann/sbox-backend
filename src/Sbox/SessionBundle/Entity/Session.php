<?php

namespace Sbox\SessionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Session
 *
 * @package Sbox\SessionBundle\Entity
 * @author  Nikita Loges
 *
 * @ORM\Entity(repositoryClass="Sbox\SessionBundle\Repository\SessionRepository")
 * @ORM\Table(name="sessions")
 */
class Session implements SessionInterface
{

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $sessionId;

    /**
     * @var resource|null
     * @ORM\Column(type="blob", nullable=true)
     */
    protected $sessionData;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $endOfLife;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        $date = new \DateTime();
        $this->id = 0;
        $this->sessionId = '';
        $this->sessionData = null;
        $this->createdAt = $date;
        $this->updatedAt = $date;
        $this->endOfLife = $date;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return void
     */
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return resource|null
     */
    public function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     * @param resource|null $sessionData
     * @return void
     */
    public function setSessionData($sessionData): void
    {
        $this->sessionData = $sessionData;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getEndOfLife(): \DateTime
    {
        return $this->endOfLife;
    }

    /**
     * @param \DateTime $endOfLife
     * @return void
     */
    public function setEndOfLife(\DateTime $endOfLife): void
    {
        $this->endOfLife = $endOfLife;
    }
}
