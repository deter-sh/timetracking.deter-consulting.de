<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\TimeTrackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimeviewController extends AbstractController
{
    #[Route('/times', name: 'app_timeview')]
    public function index(Request $request, TimeTrackRepository $timeTrackRepository, CustomerRepository $customerRepository): Response
    {
        if ((int) (new \DateTime())->format('d') >= 20) {
            $startDate = new \DateTime('20.' . (new \DateTime())->format('m.Y'));
            $endDate = new \DateTime('19.' . (new \DateTime($startDate->format('d.m.Y') . '+1month'))->format('m.Y'));
        } else {
            $startDate = new \DateTime('20.' . (new \DateTime('today - 1 month'))->format('m.Y'));
            $endDate = new \DateTime('19.' . (new \DateTime())->format('m.Y'));
        }

        $periods = [];
        for ($i = 0; $i < 12; $i++) {
            $s = new \DateTime($startDate->format('d.m.Y') . ' - ' . $i . ' months');
            $e = new \DateTime($endDate->format('d.m.Y') . ' - ' . $i . ' months');
            $periods['-'.$i] = $s->format('d.m.Y') . ' - ' . $e->format('d.m.Y');
        }

        if ($request->get('period')) {
            $startDate = $startDate->modify($request->get('period') . ' months');
            $endDate = $endDate->modify($request->get('period') . ' months');
        }

        $customer = null;
        if ($request->get('customer')) {
           $customer = $customerRepository->find($request->get('customer'));
        }

        if ($this->getUser() instanceof User && $this->getUser()->getCustomer()) {
            $customers[] = $this->getUser()->getCustomer();
            $customer = $this->getUser()->getCustomer();
        } else {
            $customers = $customerRepository->findAll();
        }

        $data = $timeTrackRepository->findInDateRange($startDate, $endDate, $customer);

        $minutes = 0;
        foreach ($data as $value) {
            $minutes += ($value->getEndtime()->getTimestamp() - $value->getStart()->getTimestamp())/60;
        }

        $hours = floor($minutes / 60);
        $minutes -= $hours * 60;
        $total = $hours . ':' . $minutes;


        return $this->render('timeview/index.html.twig', [
            'data' => $data,
            'total' => $total,
            'periods' => $periods,
            'customers' => $customers,
        ]);
    }
}
