<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class BinaryStringType extends Type
{
    const BINARYSTRING = 'binarystring';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
        if (null === $value) {
            return '';
        }

        if (is_string($value)) {
            $fp = fopen('php://temp', 'rb+');

            if ($fp) {
                fwrite($fp, $value);
                fseek($fp, 0);
                $value = $fp;
            }
        }

        if (!is_resource($value)) {
            throw ConversionException::conversionFailed($value, self::BINARY);
        }

        $stringValue = stream_get_contents($value);

        return $stringValue;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::BINARYSTRING;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindingType()
    {
        return \PDO::PARAM_LOB;
    }
}
