<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\MySuperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
