<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Datasheet\SampleDatasheet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/sample/", name="crm_sample_") */
class SampleController extends AbstractController
{
    private $entityManager;

    private $sampleDatasheet;

    public function __construct(
        EntityManagerInterface $entityManager,
        SampleDatasheet $sampleDatasheet
    )
    {
        $this->entityManager = $entityManager;
        $this->sampleDatasheet = $sampleDatasheet;
    }

    /** @Route("datasheet", name="datasheet") */
    public function index(Request $request)
    {
        $datasheet = $this->sampleDatasheet;

        return $this->render('@A2CRM/object/object.list.html.twig', [
            'datasheet' => $datasheet,
        ]);
    }
}
