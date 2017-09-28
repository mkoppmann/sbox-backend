<?php

namespace Sbox\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sbox\MessageBundle\Entity\Message;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPair;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\KeyPairUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The sbox user.
 *
 * @ORM\Table("users")
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("all")
 */
class User implements KeyPairUserInterface
{
    /**
     * @var string
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @Serializer\Expose
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=180, unique=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(name="email", type="string")
     * @Serializer\Expose
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="name", type="string")
     * @Serializer\Expose
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * The plain-text password is not persisted. It is only used to store the password in-memory during the creation of
     * a user so that their secret key kan be encrypted.
     * @var string
     */
    protected $plainTextPassword;

    /**
     * @var array
     * @ORM\Column(name="roles", type="array")
     */
    protected $roles;

    /**
     * @var ?KeyPair
     */
    protected $keyPair;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sbox\MessageBundle\Entity\Message",mappedBy="sender")
     */
    protected $messagesSender;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Sbox\MessageBundle\Entity\Message",mappedBy="recipients")
     */
    protected $messagesRecipient;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->id = '';
        $this->username = '';
        $this->email = '';
        $this->name = '';
        $this->password = '';
        $this->plainTextPassword = '';
        $this->roles = [];
        $this->keyPair = new KeyPair();
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
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return mb_convert_encoding($this->name, 'UTF-8', 'UTF-8');
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @param array $roles
     * @return void
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainTextPassword(): string
    {
        return $this->plainTextPassword;
    }

    /**
     * @param string $plainTextPassword
     */
    public function setPlainTextPassword(string $plainTextPassword): void
    {
        $this->plainTextPassword = $plainTextPassword;
    }



    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        // We'll use an algorithm that includes the salt in the hashed password.
        return null;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return null|KeyPair
     */
    public function getKeyPair(): ?KeyPair
    {
        return $this->keyPair;
    }

    /**
     * @param KeyPair $keyPair
     * @return void
     */
    public function setKeyPair(KeyPair $keyPair): void
    {
        $this->keyPair = $keyPair;
    }

    /**
     * @return Message[]
     */
    public function getMessages() : array
    {
        $messagesNotUnique = new ArrayCollection(
            array_merge(
                $this->messagesSender->toArray(),
                $this->messagesRecipient->toArray()
            )
        );
        $messages = new ArrayCollection();
        foreach ($messagesNotUnique as $message) {
            if (!$messages->contains($message)) {
                $messages->add($message);
            }
        }

        /**
         * @var Message[]
         */
        $messageArray = $messages->toArray();
        return $messageArray;
    }
}
