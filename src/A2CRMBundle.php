<?php

namespace A2Global\CRMBundle;

use A2Global\CRMBundle\DependencyInjection\A2CRMExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class A2CRMBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new A2CRMExtension();
        }

        return $this->extension;
    }
}