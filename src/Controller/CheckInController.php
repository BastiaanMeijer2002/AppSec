<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckInController extends AbstractController
{
    #[Route('/checkin', name: 'app_check_in')]
    public function checkinUser(ManagerRegistry $doctrine, UserRepository $userRepository, InvoiceRepository $invoiceRepository, Request $request): Response
    {
        $data = $request->toArray();

        if (!isset($data['gym']) && !isset($data['user'])){
            return $this->json("Please enter a gym and user");
        }

        $user = $userRepository->find($data['user']);

        if (!$user){
            return $this->json("User doesn't exist");
        }

        $membership = $user->getMembership();

        if (!$membership->isIsActive()) {
            return $this->json("Membership has been canceled", 401);
        }

        if ($membership->getCredits() == 0) {
            return $this->json("User has no credits", 401);
        }

        if ($membership->getEndDate() < new \DateTime()) {
            return $this->json("Membership has expired", 401);
        }

        $entityManager = $doctrine->getManager();

        $membership->setCredits($membership->getCredits() - 1);

        $entityManager->persist($membership);
        $entityManager->flush();

        $latestInvoice = $invoiceRepository->findLatestInvoice($membership->getId());

        $invoiceLine = new InvoiceLine();
        $invoiceLine->setAmount(1);
        $invoiceLine->setDescription("Checkin at ".$data["gym"]);

        $today = new DateTime();

        if (isset($latestInvoice[0])){
            if ($latestInvoice[0]->getDate()->format('n') === $today->format('n')) {
                $invoiceLine->setInvoice($latestInvoice[0]);
            }

        } else {
            $invoice = new Invoice();
            $invoice->setDescription("New invoice");
            $invoice->setAmount(0);
            $invoice->setMembership($membership);
            $invoice->setStatus("Outstanding");
            $invoice->setDate(new DateTime());
            $entityManager->persist($invoice);

            $invoiceLine->setInvoice($invoice);

        }
        $entityManager->persist($invoiceLine);
        $entityManager->flush();
        return $this->json("User successfully checked in", 200);


    }
}
