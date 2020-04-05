<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Provider\HeartbeatProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/", name="crm_") */
class DefaultController extends AbstractController
{
    private $entityManager;

    private $heartbeatProvider;

    public function __construct(
        EntityManagerInterface $entityManager,
        HeartbeatProvider $heartbeatProvider
    )
    {
        $this->entityManager = $entityManager;
        $this->heartbeatProvider = $heartbeatProvider;
    }

    /**
     * @Route("", name="dashboard")
     */
    public function index()
    {
        return $this->render('@A2CRM/dashboard.html.twig');
    }

    /**
     * @Route("heartbeat", name="heartbeat")
     */
    public function heartbeat()
    {
        return $this->render('@A2CRM/etc/heartbeat.html.twig', [
            'timestamp' => $this->heartbeatProvider->getTimestamp(),
        ]);
    }
}
