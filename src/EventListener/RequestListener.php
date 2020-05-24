<?php

namespace A2Global\CRMBundle\EventListener;

use A2Global\CRMBundle\Api\SlackApi;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class RequestListener
{
    const SLACK_TIMEOUT = 3;

    protected $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function onKernelException($exceptionEvent)
    {
        try {
            /** @var ExceptionEvent $exceptionEvent */
            if (!$exceptionEvent->isMasterRequest()) {
                return;
            }

            if ($this->parameterBag->has('a2crm.logger.slack.errors') && (bool)$this->parameterBag->get('a2crm.logger.slack.errors')) {
                if (
                    $this->parameterBag->has('a2crm.logger.slack.errors.ignore404')
                    && (bool)$this->parameterBag->get('a2crm.logger.slack.errors.ignore404')
                    && $exceptionEvent->getThrowable() instanceof NotFoundHttpException
                ) {
                    return;
                }

                $channel = $this->parameterBag->get('a2crm.logger.slack.channel.errors');

                if (substr($channel, 0, 1) != '#') {
                    $channel = '#' . $channel;
                }
                $message = sprintf(
                    '%s%s%s (%s:%s)',
                    $this->getRequestInfo($exceptionEvent->getRequest()),
                    PHP_EOL,
                    $exceptionEvent->getThrowable()->getMessage(),
                    $exceptionEvent->getThrowable()->getFile(),
                    $exceptionEvent->getThrowable()->getLine()
                );

                (new SlackApi($this->parameterBag->get('a2crm.logger.slack.token')))
                    ->message($channel, $message, self::SLACK_TIMEOUT);
            }
        } catch (throwable $e) {
        }
    }

    public function onKernelResponse(ResponseEvent $responseEvent)
    {
        if (!$responseEvent->isMasterRequest()) {
            return;
        }

        try {
            if ($this->parameterBag->has('a2crm.logger.slack.requests') && (bool)$this->parameterBag->get('a2crm.logger.slack.requests')) {
                if (
                    $this->parameterBag->has('a2crm.logger.slack.requests.ignore404')
                    && (bool)$this->parameterBag->get('a2crm.logger.slack.requests.ignore404')
                    && $responseEvent->getResponse()->getStatusCode() == 404
                ) {
                    return;
                }

                $channel = $this->parameterBag->get('a2crm.logger.slack.channel.requests');

                if (substr($channel, 0, 1) != '#') {
                    $channel = '#' . $channel;
                }
                $message = sprintf(
                    '%s %s %s',
                    $responseEvent->getResponse()->getStatusCode(),
                    $responseEvent->getRequest()->getMethod(),
                    $responseEvent->getRequest()->getUri()
                );

                (new SlackApi($this->parameterBag->get('a2crm.logger.slack.token')))
                    ->message($channel, $message, self::SLACK_TIMEOUT);
            }
        } catch (Throwable $exception) {
        }
    }

    protected function getRequestInfo(Request $request)
    {
        return sprintf(
            '%s %s',
            $request->getMethod(),
            $request->getUri()
        );
    }
}