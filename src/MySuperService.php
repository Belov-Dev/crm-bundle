<?php

namespace A2Global\CRMBundle;

use DateTime;

class MySuperService
{
    public function getDate()
    {
        return (new DateTime())->format(DATE_RFC7231);
    }
}