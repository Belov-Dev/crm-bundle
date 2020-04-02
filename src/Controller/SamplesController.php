<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\DataGrid\SampleCustomDataGrid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin/samples/", name="crm_samples_") */
class SamplesController extends AbstractController
{
    private $entityManager;

    private $sampleCustomDataGrid;

    public function __construct(
        EntityManagerInterface $entityManager,
        SampleCustomDataGrid $sampleCustomDataGrid
    )
    {
        $this->entityManager = $entityManager;
        $this->sampleCustomDataGrid = $sampleCustomDataGrid;
    }

    /**
     * @Route("custom-datagrid", name="custom_datagrid")
     */
    public function index(Request $request)
    {
        $dataGrid = $this->sampleCustomDataGrid->build($request->query->all());

        return $this->render('@A2CRM/samples/datagrid.html.twig', [
            'dataGrid' => $dataGrid,
        ]);
    }
}
