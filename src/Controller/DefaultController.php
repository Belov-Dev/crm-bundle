<?php

namespace A2Global\CRMBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/", name="crm_") */
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
     * @Route("", name="dashboard")
     */
    public function index()
    {
        return $this->render('@A2CRM/dashboard.html.twig');
    }
}
