<?php

namespace Sbox\MessageBundle\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Sbox\MessageBundle\Entity\Message;
use Sbox\UserBundle\Entity\User;

class MessageManager implements MessageManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var ObjectRepository
     */
    protected $messageRepository;

    /**
     * MessageManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $this->entityManager->getRepository('SboxMessageBundle:Message');
    }

    /**
     * Finds all messages by the given criteria
     *
     * @param array $criteria
     * @param User $user
     * @return array|null
     */
    public function findMessageBy(array $criteria, User $user): ?array
    {
        try {
            /**
             * @var Message[]
             */
            $messages = $this->messageRepository->findBy($criteria);
            $messages = $this->filterByUser($messages, $user);
        } catch (DriverException $driverException) {
            $messages = null;
        }

        return $messages;
    }

    /**
     * @param string $id
     * @param User $user
     * @return null|Message
     */
    public function findMessageById(string $id, User $user): ?Message
    {
        try {
            /**
             * @var Message
             */
            $message = $this->messageRepository->findOneBy(["id" => $id]);
            if (!$message || !$this->isUserInMessage($message, $user)) {
                return null;
            } else {
                return $message;
            }
        } catch (DriverException $e) {
        }

        return null;
    }

    /**
     * @param User $user
     * @return Message[]|null
     */
    public function getMessages(User $user): ?array
    {
        /**
         * @var Message[]
         */
        $messages = $this->messageRepository->findAll();

        $messages = $this->filterByUser($messages, $user);

        return $messages;
    }

    /**
     * @param Message $message
     */
    public function deleteMessage($message): void
    {
        $this->entityManager->remove($message);
        $this->entityManager->flush();
    }

    /**
     * @param Message[] $messages
     * @param User $user
     * @return Message[]|null
     */
    private function filterByUser(array $messages, User $user): ?array
    {
        if (!$messages || !$user) {
            return null;
        }



        $checkedMessages = array();
        foreach ($messages as $message) {
            if ($this->isUserInMessage($message, $user)) {
                array_push($checkedMessages, $message);
            }
        }
        return $checkedMessages;
    }

    /**
     * @param Message $message
     * @param User $user
     * @return bool
     */
    private function isUserInMessage(Message $message, User $user): bool
    {
        if ($message->getSender() == $user) {
            return true;
        }

        foreach ($message->getRecipients() as $recipient) {
            if ($recipient == $user) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $subject
     * @param User $sender
     * @param User[] $recipients
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
    ): ?Message {
        $message = new Message();
        $message->setSubject($subject);
        $message->setSender($sender);
        $message->setRecipients($recipients);
        $message->setDate($date);
        $message->setMessageBody($messageBody);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        if (!$message->getId()) {
            return null;
        }
        if (sizeof($message->getId()) == 0) {
             return null;
        }

        return $message;
    }
}
