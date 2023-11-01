<?php

namespace App\Controller;

use src\Entity\Invoice;
use src\Entity\InvoiceLine;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceLineController extends AbstractController
{
    #[Route('/invoice-line/create', methods: "POST")]
    public function createInvoiceLine(ManagerRegistry $doctrine, Request $request): Response
    {
        $data = $request->toArray();

        if (!isset($data['amount']) || !isset($data['description']) || !isset($data['invoice'])) {
            return $this->json("Please enter a description, amount and invoice");
        }

        $invoice = $doctrine->getRepository(Invoice::class)->find($data["invoice"]);

        if (!$invoice) {
            return $this->json("Invoice line doesn't exist");
        }

        $description = $data["description"];
        $amount = $data["amount"];

        $invoiceLine = new InvoiceLine();
        $invoiceLine->setDescription($description);
        $invoiceLine->setAmount($amount);
        $invoice->addInvoiceLine($invoiceLine);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($invoiceLine);
        $entityManager->flush();

        return $this->json([
            "id" => $invoiceLine->getId(),
            "amount" => $invoiceLine->getAmount(),
            "description" => $invoiceLine->getDescription(),
            "invoice" => $invoiceLine->getInvoice()->getId()
        ], 201);

    }

    #[Route('invoice-line/update/{id}', methods: "POST")]
    public function updateInvoiceLine(ManagerRegistry $doctrine, Request $request, $id)
    {
        $invoiceLine = $doctrine->getRepository(InvoiceLine::class)->find($id);

        if (!$invoiceLine) {
            return $this->json("Invoice line doesn't exist");
        }

        $data = $request->toArray();

        if (isset($data['description'])){
            $invoiceLine->setDescription($data['description']);
        }

        if (isset($data['amount'])){
            $invoiceLine->setAmount($data['amount']);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($invoiceLine);
        $entityManager->flush();

        return $this->json([
            "id" => $invoiceLine->getId(),
            "amount" => $invoiceLine->getAmount(),
            "description" => $invoiceLine->getDescription(),
            "invoice" => $invoiceLine->getInvoice()->getId()
        ], 201);
    }

    #[Route('/invoice-line/delete/{id}')]
    public function removeInvoiceLine(ManagerRegistry $doctrine, $id)
    {
        $invoiceLine = $doctrine->getRepository(InvoiceLine::class)->find($id);

        if (!$invoiceLine) {
            return $this->json("Invoice line doesn't exist");
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($invoiceLine);
        $entityManager->flush();

        return $this->json("Invoice deleted!");
    }

    #[Route('/invoice-line/{id}', methods: "GET")]
    public function getInvoiceLine(ManagerRegistry $doctrine, $id)
    {
        $invoiceLine = $doctrine->getRepository(InvoiceLine::class)->find($id);

        if (!$invoiceLine) {
            return $this->json("Invoice line doesn't exist");
        }

        return $this->json([
            "id" => $invoiceLine->getId(),
            "amount" => $invoiceLine->getAmount(),
            "description" => $invoiceLine->getDescription(),
            "invoice" => $invoiceLine->getInvoice()->getId()
        ], 201);
    }

    #[Route('/invoice-lines', methods: "GET")]
    public function getAllInvoiceLines(ManagerRegistry $doctrine)
    {
        $invoiceLines = $doctrine->getRepository(InvoiceLine::class)->findAll();

        $data = [];
        foreach ($invoiceLines as $invoiceLine) {
            $data[] = [
                "id" => $invoiceLine->getId(),
                "description" => $invoiceLine->getDescription(),
                "amount" => $invoiceLine->getAmount(),
                "invoice" => $invoiceLine->getInvoice()->getId()
            ];
        }

        return $this->json($data);
    }
}
