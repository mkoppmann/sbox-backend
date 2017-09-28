<?php

namespace SboxUserSpecificEntityEncryptionBundle\Subscriber\Encryption;

use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sbox\MessageBundle\Entity\Message;
use Sbox\UserBundle\Entity\User;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPairUserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AbstractEncryptionDecryptionSubscriberTest extends WebTestCase
{
    /** @var  KeyPairUserInterface */
    protected $currentUser;

    /** @var  EntityManagerInterface */
    protected $entityManager;

    /** @var  string */
    protected $subject;

    /** @var  string */
    protected $subject2;

    /** @var  string */
    protected $messageBody;

    /** @var  string */
    protected $messageBody2;

    /** @var  string */
    protected $username;



    /** @var User */
    protected $sender;

    /** @var string */
    private $testPassword = "TestP@ssw0rd!";

    public function setUp()
    {
        $this->subject = 'The very secret subject';
        $this->messageBody = 'This is the message that has to be kept very secret.';
        $this->subject2 = 'The other very secret subject';
        $this->messageBody2 = 'This is the other message that has to be kept very secret.';
        $this->username = 'testuser';

        $this->loadFixtures([
            'Sbox\UserBundle\DataFixtures\ORM\LoadUserData',
        ]);

        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var KeyPairUserInterface $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => $this->username
        ]);

        $this->sender = $user;

        $token = new UsernamePasswordToken($user, $this->testPassword, 'sbox_users', ['ROLE_USER']);

        $this->getContainer()->get('security.token_storage')->setToken($token);

        $user->getKeyPair()->setCachedMasterSecret(
            $user->getKeyPair()->getMasterSecretFromPassword($this->testPassword)
        );
    }

    public function testPrePersist()
    {
        $message = new Message();
        $message->setSubject($this->subject);
        $message->setMessageBody($this->messageBody);
        $message->setSender($this->sender);

        // First, we persist the message, and then everything should be encrypted in memory.
        $this->entityManager->persist($message);

        // The plain text and the ciphertext should not match.
        $this->assertNotEquals($this->subject, $message->getSubject());
        $this->assertNotEquals($this->messageBody, $message->getMessageBody());

        // The ciphertext is supposed to be binary, so it shouldn't be ASCII (this is highly unlikely).
        $this->assertFalse(mb_detect_encoding($message->getSubject(), 'ASCII', true));
        $this->assertFalse(mb_detect_encoding($message->getMessageBody(), 'ASCII', true));

        // After flushing (inserting) the message, the cleartext of the properties should be restored in memory.
        $this->entityManager->flush();

        $this->assertEquals($this->subject, $message->getSubject());
        $this->assertEquals($this->messageBody, $message->getMessageBody());

        $this->assertEquals(mb_detect_encoding($message->getSubject(), 'ASCII', true), 'ASCII');
        $this->assertEquals(mb_detect_encoding($message->getMessageBody(), 'ASCII', true), 'ASCII');
    }

    public function testCleartextCache()
    {
        $message = new Message();
        $message->setSubject($this->subject);
        $message->setMessageBody($this->messageBody);
        $message->setSender($this->sender);


        $this->entityManager->persist($message);

        // The plain text and the ciphertext should not match.
        $this->assertNotEquals($this->subject, $message->getSubject());
        $this->assertNotEquals($this->messageBody, $message->getMessageBody());

        // The ciphertext is supposed to be binary, so it shouldn't be ASCII (this is highly unlikely).
        $this->assertFalse(mb_detect_encoding($message->getSubject(), 'ASCII', true));
        $this->assertFalse(mb_detect_encoding($message->getMessageBody(), 'ASCII', true));

        $this->entityManager->flush($message);

        $this->assertEquals($this->subject, $message->getSubject());
        $this->assertEquals($this->messageBody, $message->getMessageBody());

        // Test postUpdate event.
        $message->setSubject($this->subject2);
        $message->setMessageBody($this->messageBody2);

        $this->entityManager->persist($message);
        $this->entityManager->flush($message);

        $this->assertEquals($this->subject2, $message->getSubject());
        $this->assertEquals($this->messageBody2, $message->getMessageBody());
    }

    public function testDecryption()
    {
        $message = new Message();
        $message->setSubject($this->subject);
        $message->setMessageBody($this->messageBody);
        $message->setSender($this->sender);


        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
        $this->entityManager->refresh($message);

        // The original plain text and the decrypted values should match.
        $this->assertEquals($this->subject, $message->getSubject());
        $this->assertEquals($this->messageBody, $message->getMessageBody());
    }
}
