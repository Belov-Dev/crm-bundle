<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Factory\DatasheetFactory;
use A2Global\CRMBundle\Factory\FormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/crm/samples", name="crm_samples_") */
class SamplesController extends AbstractController
{
    private $entityManager;

    private $formFactory;

    private $datasheetFactory;
    private $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        DatasheetFactory $datasheetFactory,
        FormFactory $formFactory,
        $projectDir
    )
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->datasheetFactory = $datasheetFactory;
        $this->projectDir = $projectDir;
    }

    /** @Route("", name="homepage") */
    public function index()
    {
        $dir = $this->projectDir . '/vendor/a2global/crm-bundle';
        $arrayDatasheet = $this->datasheetFactory->get()
            ->setData([
                ['id' => 1, 'name' => 'Alpha'],
                ['id' => 2, 'name' => 'Bravo'],
                ['id' => 3, 'name' => 'Charlie'],
            ])
            ->setData(function () use ($dir) {
                $i = 0;
                $items = [];

                foreach (glob($dir . '/{,*/*,*/*/*,*/*/*/*}', GLOB_BRACE) as $file) {
                    ++$i;
                    $items[] = [
                        'id' => $i,
                        'name' => basename($file),
                        'path' => $file,
                        'size' => filesize($file),
                    ];
                }

                return $items;
            })
//            ->removeFields()
//            ->addField('id')
//            ->addField('name', 'File name')
        ;

        return $this->render('@A2CRM/samples/homepage.html.twig', [
            'arrayDatasheet' => $arrayDatasheet,
        ]);
    }
}