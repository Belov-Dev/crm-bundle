<?php

namespace A2Global\CRMBundle\Controller;

use A2Global\CRMBundle\Factory\DatasheetFactory;
use A2Global\CRMBundle\Factory\FormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        $entityManager = $this->entityManager;
        /** @var EntityRepository $workerRepository */
        $workerRepository = $this->entityManager->getRepository('App:Worker');

        $arrayDatasheet = $this->datasheetFactory->get()
            ->setQueryBuilder(function() use ($workerRepository){
                return $workerRepository->createQueryBuilder('w')
                    ->andWhere('w.birthday < :date')
                    ->setParameter('date', '1980-01-01');
            })
            ->setFields('id', 'gender', 'firstName', 'lastName');

        return $this->render('@A2CRM/samples/homepage.html.twig', [
            'arrayDatasheet' => $arrayDatasheet,
        ]);
    }
}