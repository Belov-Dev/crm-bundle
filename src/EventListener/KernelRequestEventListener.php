<?php

namespace A2Global\CRMBundle\EventListener;

class KernelRequestEventListener
{
    public function onKernelRequest()
    {
        $proxyClassesDirectory = __DIR__ . '/../../var/cache/a2crm';

        foreach (glob($proxyClassesDirectory . '/*') as $proxyClass) {
            if(!class_exists('App\\Entity\\'.ucfirst($proxyClass))){
                require_once $proxyClass;
            }
        }
    }
}