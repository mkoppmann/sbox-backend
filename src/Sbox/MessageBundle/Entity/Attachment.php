<?php

namespace Sbox\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sbox\UserSpecificEntityEncryptionBundle\Entity\EncryptableEntity;
use Sbox\UserSpecificEntityEncryptionBundle\Annotation\Encrypted;

/**
 * @ORM\Entity
 */
class Attachment extends EncryptableEntity
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
    protected $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->id = '';
        $this->fileName = '';
    }
}
