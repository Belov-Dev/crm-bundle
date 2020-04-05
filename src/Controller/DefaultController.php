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

    private $projectDir;

    private $cacheDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        HeartbeatProvider $heartbeatProvider,
        $projectDir,
        $cacheDir
    )
    {
        $this->entityManager = $entityManager;
        $this->heartbeatProvider = $heartbeatProvider;
        $this->projectDir = $projectDir;
        $this->cacheDir = $cacheDir;
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

    /**
     * @Route("info", name="info")
     */
    public function info()
    {
        return $this->render('@A2CRM/etc/info.html.twig', [
            'projectDir' => $this->projectDir,
            'cacheDir' => $this->cacheDir,
        ]);
    }
}
