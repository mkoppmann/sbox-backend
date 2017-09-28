<?php

namespace Sbox\UserSpecificEntityEncryptionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sbox\UserSpecificEntityEncryptionBundle\DependencyInjection\SboxUserSpecificEntityEncryptionBundleExtension;

class SboxUserSpecificEntityEncryptionBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SboxUserSpecificEntityEncryptionBundleExtension();
    }
}
