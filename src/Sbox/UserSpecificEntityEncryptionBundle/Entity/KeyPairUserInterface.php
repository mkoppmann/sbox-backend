<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

interface KeyPairUserInterface extends UserInterface
{
    /**
     * Gets the plain-text password of the user.
     * @return string
     */
    public function getPlainTextPassword(): string;

    /**
     * Sets the plain-text password of the user.
     * @param string $plainTextPassword
     */
    public function setPlainTextPassword(string $plainTextPassword): void;

    /**
     * @return null|KeyPair
     */
    public function getKeyPair(): ?KeyPair;

    /**
     * @param KeyPair $keyPair
     */
    public function setKeyPair(KeyPair $keyPair): void;
}
