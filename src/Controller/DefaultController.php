<?php

namespace A2Global\CRMBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/manage", name="a2crm_homepage")
     */
    public function index()
    {
        return $this->render('@A2CRM/homepage.html.twig');
    }

    /**
     * @Route("/manage/heartbeat", name="a2crm_hearbeat")
     */
    public function heartbeat()
    {
        return new Response(sprintf('Heartbeat: OK [%s]', (new DateTime())->format(DATE_RFC7231)));
    }
}
