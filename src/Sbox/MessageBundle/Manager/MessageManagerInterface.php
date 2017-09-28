<?php

namespace Sbox\MessageBundle\Manager;

use Sbox\MessageBundle\Entity\Message;
use Sbox\UserBundle\Entity\User;

interface MessageManagerInterface
{
    /**
     * Finds all messages by the given criteria
     *
     * @param array $criteria
     * @param User $user
     * @return Message[]|null
     */
    public function findMessageBy(array $criteria, User $user): ?array;

    /**
     * @param string $id
     * @param User $user
     * @return null|Message
     */
    public function findMessageById(string $id, User $user): ?Message;

    /**
     * @param User $user
     * @return Message[]|null
     */
    public function getMessages(User $user): ?array;

    /**
     * @param Message $message
     */
    public function deleteMessage($message): void;

    /**
     * @param string $subject
     * @param User $sender
     * @param array $recipients
     * @param \DateTime $date
     * @param string $messageBody
     * @return Message|null
     */
    public function createMessage(
        string $subject,
        User $sender,
        array $recipients,
        \DateTime $date,
        string $messageBody
    ): ?Message;
}
