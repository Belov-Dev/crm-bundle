<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Api\SlackApi;
use A2Global\CRMBundle\Logger\RequestLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Throwable;

class RequestListener
{
    const SLACK_TIMEOUT = 3;

    protected $requestLogger;

    protected $parameterBag;

    protected $slackApi;

    public function __construct(RequestLogger $requestLogger, SlackApi $slackApi, ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->slackApi = $slackApi;
        $this->requestLogger = $requestLogger;
    }

    public function onKernelRequest(RequestEvent $requestEvent)
    {
        if (!$requestEvent->isMasterRequest()) {
            return;
        }

        try {
            if ($this->parameterBag->has('a2crm.logger.slack.requests') && (bool)$this->parameterBag->get('a2crm.logger.slack.requests')) {
                $channel = $this->parameterBag->get('a2crm.logger.slack.channel.requests');

                if (substr($channel, 0, 1) != '#') {
                    $channel = '#' . $channel;
                }
                $message = sprintf(
                    '%s %s',
                    $requestEvent->getRequest()->getMethod(),
                    $requestEvent->getRequest()->getUri()
                );

                (new SlackApi($this->parameterBag->get('a2crm.logger.slack.token')))
                    ->message($channel, $message, self::SLACK_TIMEOUT);
            }
        } catch (Throwable $exception) {
        }
    }

    public function onKernelException($exceptionEvent)
    {
        try {
            /** @var ExceptionEvent $exceptionEvent */
            if (!$exceptionEvent->isMasterRequest()) {
                return;
            }

            if ($this->parameterBag->has('a2crm.logger.slack.errors') && (bool)$this->parameterBag->get('a2crm.logger.slack.errors')) {
                $channel = $this->parameterBag->get('a2crm.logger.slack.channel.errors');

                if (substr($channel, 0, 1) != '#') {
                    $channel = '#' . $channel;
                }
                $message = sprintf(
                    '%s (%s:%s)',
                    $exceptionEvent->getThrowable()->getMessage(),
                    $exceptionEvent->getThrowable()->getFile(),
                    $exceptionEvent->getThrowable()->getLine()
                );

                (new SlackApi($this->parameterBag->get('a2crm.logger.slack.token')))
                    ->message($channel, $message, self::SLACK_TIMEOUT);
            }
        } catch (throwable $e) {
            // todo catch this case
        }
    }

    public function onKernelResponse($responseEvent)
    {
        return;
        if (!$this->apiLoggingEnable) {
            return;
        }

        try {
            /** @var ResponseEvent $responseEvent */
            if (!$responseEvent->isMasterRequest()) {
                return;
            }
            if (!preg_match('/^api\./i', $responseEvent->getRequest()->getHost())) {
                return;
            }
            $this->logger->registerResponse($responseEvent->getResponse());
        } catch (throwable $e) {
            // todo catch this case
        }
    }
}