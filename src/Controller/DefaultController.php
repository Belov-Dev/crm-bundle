<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\MySuperService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $mySuperService;

    public function __construct(MySuperService $mySuperService)
    {
        $this->mySuperService = $mySuperService;
    }

    /**
     * @Route("/manage", name="a2crm_homepage")
     */
    public function index()
    {
        return $this->render('@A2CRM/homepage.html.twig', [
        ]);
    }

    /**
     * @Route("/manage/heartbeat", name="a2crm_hearbeat")
     */
    public function heartbeat()
    {
        return new Response(sprintf('Heartbeat: OK [%s]', (new DateTime())->format(DATE_RFC7231)));
    }
}
