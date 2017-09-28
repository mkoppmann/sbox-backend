<?php

namespace Sbox\MessageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptableEntity;
use Sbox\UserSpecificEntityEncryptionBundle\Annotation\Encrypted;
use Sbox\UserBundle\Entity\User;

/**
 * @ORM\Entity
 */
class Message extends EncryptableEntity
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     *
     * @Encrypted
     */
    protected $subject;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Sbox\UserBundle\Entity\User",cascade={"persist"},inversedBy="messagesSender")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     */
    protected $sender;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Sbox\UserBundle\Entity\User",inversedBy="messagesRecipient",cascade={"persist"})
     * @ORM\JoinTable(name="messages_recipients",
     *     joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="recipient_id", referencedColumnName="id")}
     *     )
     */
    protected $recipients;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz")
     */
    protected $date;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Serializer\SerializedName("message")
     *
     * @Encrypted
     */
    protected $messageBody;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = '';
        $this->subject = '';
        $this->messageBody = '';
        $this->sender = new User();
        $this->recipients = new ArrayCollection();
        $this->date = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessageBody(): string
    {
        return $this->messageBody;
    }

    /**
     * @param string $messageBody
     */
    public function setMessageBody(string $messageBody): void
    {
        $this->messageBody = $messageBody;
    }

    /**
     * @param User $sender
     */
    public function setSender(User $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return User
     */
    public function getSender(): User
    {
        return $this->sender;
    }

    /**
     * @param User[] $recipients
     */
    public function setRecipients(array $recipients): void
    {
        //TODO: validation check
        $this->recipients = new ArrayCollection($recipients);
    }

    /**
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->recipients->toArray();
    }

    /**
     * @param $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
