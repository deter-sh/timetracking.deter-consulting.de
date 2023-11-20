<?php

namespace App\Controller;

use App\Entity\TimeTrack;
use App\Entity\User;
use App\Form\EndTaskType;
use App\Form\StartTaskType;
use App\Repository\CustomerRepository;
use App\Repository\TimeTrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(Request $request, TimeTrackRepository $timeTrackRepository, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() instanceof User && $this->getUser()->getCustomer()) {
            return $this->redirect($this->generateUrl('app_timeview'));
        }

        $openTask = false;

        if ($entity = $timeTrackRepository->findOpen()) {
            $form = $this->createForm(EndTaskType::class, $entity);
            $openTask = true;
        } else {
            $entity = new TimeTrack();
            $form = $this->createForm(StartTaskType::class, $entity);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($openTask) {
                $entity->setEndtime($this->removeSeconds(new \DateTime()));
            } else {
                $entity->setStart($this->removeSeconds(new \DateTime()));
            }
            $entityManager->persist($entity);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('app_dashboard'));
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
            'openTask' => $openTask,
        ]);
    }

    public function removeSeconds(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        if (!$dateTime instanceof \DateTime) {
            throw new \InvalidArgumentException('Invalid date object.');
        }

        return $dateTime->setTime(
            (int)$dateTime->format('G'), // hours
            (int)$dateTime->format('i'), // minutes
            0  // seconds
        );
    }
}
