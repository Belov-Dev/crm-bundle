<?php

namespace A2Global\CRMBundle\Api;

use Exception;
use GuzzleHttp\Client;

class SlackApi
{
    private $token;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    public function message($channel, $message, $timeout = 60)
    {
        if(!$this->token){
            throw new Exception('Token is required for SlackAPI');
        }

        $result = (new Client())->post('https://slack.com/api/chat.postMessage', [
            'form_params' => [
                'token' => $this->token,
                'channel' => $channel,
                'text' => $message,
            ],
            'connect_timeout' => $timeout,
        ]);
    }
}