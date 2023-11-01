<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Membership;
use App\Repository\InvoiceRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class InvoiceController extends AbstractController
{
    #[Route('/invoice/create', name: 'app_invoice_create', methods: "POST")]
    public function createInvoice(ManagerRegistry $doctrine, Request $request): Response
    {
        $data = $request->toArray();

        if (!isset($data['amount']) && !isset($data['description']) && !isset($data['membership'])) {
            return $this->json("Please enter a description, amount, and membership");
        }

        $membership = $doctrine->getRepository(Membership::class)->find($data["membership"]);

        if (!$membership){
            return $this->json("membership not found");
        }

        $status = "Outstanding";
        $description = $data["description"];
        $amount = $data["amount"];
        $date = new \DateTime();

        $invoice = new Invoice();
        $invoice->setDescription($description);
        $invoice->setStatus($status);
        $invoice->setAmount($amount);
        $invoice->setDate($date);
        $invoice->setMembership($membership);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($invoice);
        $entityManager->flush();

        return $this->json([
            "id" => $invoice->getId(),
            "description" => $invoice->getDescription(),
            "status" => $invoice->getStatus(),
            "amount" => $invoice->getAmount(),
            "date" => $invoice->getDate(),
            "membership" => $invoice->getMembership()->getId()
        ], 201);

    }

    #[Route('invoice/update/{id}', name: 'app_invoice_update', methods: "POST")]
    public function updateInvoice(ManagerRegistry $doctrine, Request $request, $id)
    {
        $invoice = $doctrine->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json("Invoice doesn't exist");
        }

        $data = $request->toArray();

        if (isset($data['description'])){
            $invoice->setDescription($data['description']);
        }

        if (isset($data['amount'])){
            $invoice->setAmount($data['amount']);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($invoice);
        $entityManager->flush();

        return $this->json([
            "id" => $invoice->getId(),
            "description" => $invoice->getDescription(),
            "status" => $invoice->getStatus(),
            "amount" => $invoice->getAmount(),
            "date" => $invoice->getDate(),
            "membership" => $invoice->getMembership()->getId()
        ], 201);
    }

    #[Route('/invoice/delete/{id}', name: "app_invoice_delete")]
    public function removeInvoice(ManagerRegistry $doctrine, $id)
    {
        $invoice = $doctrine->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json("Invoice doesn't exist");
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($invoice);
        $entityManager->flush();

        return $this->json("Invoice deleted!");
    }

    #[Route('/invoice/{id}', methods: "GET")]
    public function getInvoice(ManagerRegistry $doctrine, $id)
    {
        $invoice = $doctrine->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json("Invoice doesn't exist");
        }

        return $this->json([
            "id" => $invoice->getId(),
            "description" => $invoice->getDescription(),
            "status" => $invoice->getStatus(),
            "amount" => $invoice->getAmount(),
            "date" => $invoice->getDate(),
            "membership" => $invoice->getMembership()->getId()
        ], 201);
    }

    #[Route('/invoices', methods: "GET")]
    public function getAllInvoices(ManagerRegistry $doctrine)
    {
        $invoices = $doctrine->getRepository(Invoice::class)->findAll();

        $data = [];
        foreach ($invoices as $invoice) {
            $data[] = [
                "id" => $invoice->getId(),
                "description" => $invoice->getDescription(),
                "status" => $invoice->getStatus(),
                "amount" => $invoice->getAmount(),
                "date" => $invoice->getDate(),
                "membership" => $invoice->getMembership()->getId()
            ];
        }

        return $this->json($data);
    }

    #[Route('/invoice/state/{id}/{status}', methods: "POST")]
    public function changeInvoiceStatus(ManagerRegistry $doctrine, $id, $status)
    {
        $invoice = $doctrine->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json("Invoice doesn't exist");
        }

        if ($status != "Outstanding" && $status != "Paid" && $status != "Void"){
            return $this->json("Enter valid status");
        }

        $invoice->setStatus($status);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($invoice);
        $entityManager->flush();

        return $this->json([
            "id" => $invoice->getId(),
            "description" => $invoice->getDescription(),
            "status" => $invoice->getStatus(),
            "amount" => $invoice->getAmount(),
            "date" => $invoice->getDate(),
            "membership" => $invoice->getMembership()->getId()
        ], 201);
    }

    #[Route('/invoice/state/{id}', methods: "GET")]
    public function getInvoiceState(ManagerRegistry $doctrine, $id)
    {
        $invoice = $doctrine->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->json("Invoice doesn't exist");
        }

        return $this->json($invoice->getStatus());
    }
}
