<?php

namespace Sbox\UserBundle\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Sbox\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager implements UserManagerInterface
{
    const SALT = "";
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    /**
     * @var  ObjectRepository
     */
    protected $userRepository;
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;
    /**
     * @var PasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * UserManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EntityManagerInterface $entityManager, EncoderFactoryInterface $encoderFactory)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $this->entityManager->getRepository('SboxUserBundle:User');
        $this->encoderFactory = $encoderFactory;
        $this->passwordEncoder = $this->encoderFactory->getEncoder(User::class);
    }

    /**
     * @param array $criteria
     * @return User|null
     */
    public function findUserBy(array $criteria): ?User
    {
        try {
            $user = $this->userRepository->findOneBy($criteria);
        } catch (DriverException $driverException) {
            $user = null;
        }

        if ($user instanceof User) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * @return array //Users[]
     */
    public function getUsers(): array
    {
        $users = $this->userRepository->findAll();

        return $users;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $name
     * @param bool $isSuperAdmin
     * @return User
     */
    public function createUser(
        string $username,
        string $password,
        string $email,
        string $name,
        bool $isSuperAdmin
    ): User {
        $user = new User();
        $user->setUsername($username);
        $pwHash = $this->passwordEncoder->encodePassword($password, $this::SALT);
        $user->setPassword($pwHash);
        $user->setPlainTextPassword($password);
        $user->setEmail($email);
        $user->setName($name);

        if ($isSuperAdmin) {
            $user->setRoles(["ROLE_SUPER_ADMIN"]);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return void
     */
    public function deleteUser(UserInterface $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
