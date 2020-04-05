<?php

namespace A2Global\CRMBundle\Provider;

use DateTime;

class HeartbeatProvider
{
    public function getTimestamp(): string
    {
        return (new DateTime())->format(DATE_RFC7231);
    }
}