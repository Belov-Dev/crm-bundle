<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Api\SlackApi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Security;
use Throwable;

class RequestListener
{
    const SLACK_TIMEOUT = 3;

    protected $error = null;

    protected $parameterBag;

    protected $security;

    public function __construct(ParameterBagInterface $parameterBag, Security $security)
    {
        $this->parameterBag = $parameterBag;
        $this->security = $security;
    }

    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        if (!$exceptionEvent->isMasterRequest()) {
            return;
        }
        $this->error = $exceptionEvent->getThrowable();
    }

    public function onKernelResponse(ResponseEvent $responseEvent)
    {
        if (!$responseEvent->isMasterRequest()) {
            return;
        }

        try {
            $this->sendLog($responseEvent->getRequest(), $responseEvent->getResponse());
        } catch (Throwable $exception) {
            $a = $exception;
        }
    }

    protected function sendLog(Request $request, Response $response): bool
    {
        if (!$this->parameterBag->has('a2crm')) {
            return false;
        }
        $parameters = $this->parameterBag->get('a2crm');

        foreach (['enabled', 'token', 'channels'] as $var) {
            if (!isset($parameters['logger']['slack'][$var]) || empty($parameters['logger']['slack'][$var])) {
                return false;
            }
        }

        foreach ($parameters['logger']['slack']['channels'] as $channelName => $channelOptions) {
            if (!$this->shouldSendToChannel($channelOptions ?? [], $request, $response)) {
                continue;
            }
            $this->send($channelName, $request, $response);
        }
    }

    protected function shouldSendToChannel(array $channel, Request $request, Response $response): bool
    {
        $excludeMethods = $channel['exclude_methods'] ?? [];

        foreach ($excludeMethods as $excludeMethod) {
            if ($request->getMethod() == strtoupper($excludeMethod)) {
                return false;
            }
        }

        $excludeCodes = $channel['exclude_codes'] ?? [];

        foreach ($excludeCodes as $excludeCode) {
            if ($response->getStatusCode() == $excludeCode) {
                return false;
            }
        }

        return true;
    }

    protected function send(string $channelName, Request $request, Response $response)
    {
        $message = sprintf(
            '*%s* `%s:%s` %s',
            $this->security->getUser() ? ucfirst($this->security->getUser()->getUsername()) : 'Guest',
            $request->getMethod(),
            $response->getStatusCode(),
            $request->getUri()
        );

        if ($this->error instanceof Throwable) {
            $message .= PHP_EOL . sprintf(
                    '%s (%s#%s)',
                    $this->error->getMessage(),
                    $this->error->getFile(),
                    $this->error->getLine()
                );
        }

        $parameters = $this->parameterBag->get('a2crm');

        (new SlackApi($parameters['logger']['slack']['token']))->message($channelName, $message, self::SLACK_TIMEOUT);
    }
}