<?php

namespace App\Controller;

use App\Entity\Membership;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MembershipController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('memberships/create', methods: "POST")]
    public function createMembership(ManagerRegistry $doctrine, Request $request)
    {
        $data = $request->toArray();

        if (!isset($data["start-date"]) && !isset($data["end-date"]) && !isset($data["user"])){
            return $this->json("Please enter start date, end date and user");
        }

        $startDate = new \DateTime($data["start-date"]);
        $endDate = new \DateTime($data["end-date"]);

        $user = $doctrine->getRepository(User::class)->find($data["user"]);

        if (!$user) {
            return $this->json("User not found");
        }

        $membership = new Membership();
        $membership->setCredits(0);
        $membership->setEndDate($endDate);
        $membership->setStartDate($startDate);
        $membership->setIsActive(true);
        $membership->setUserId($user);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($membership);
        $entityManager->flush();

        return $this->json($membership);
    }

    #[Route("memberships/deactivate/{id}")]
    public function deactivateMembership(ManagerRegistry $doctrine, $id)
    {
        $membership = $doctrine->getRepository(Membership::class)->find($id);

        if (!$membership) {
            return $this->json("Membership doesnt exist!");
        }

        $membership->setIsActive(false);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($membership);
        $entityManager->flush();

        return $this->json("Membership ".$membership->getId()." has been deactivated");

    }

    #[Route("memberships/activate/{id}")]
    public function activateMembership(ManagerRegistry $doctrine, $id)
    {
        $membership = $doctrine->getRepository(Membership::class)->find($id);

        if (!$membership) {
            return $this->json("Membership doesnt exist!");
        }

        $membership->setIsActive(true);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($membership);
        $entityManager->flush();

        return $this->json("Membership ".$membership->getId()." has been activated");
    }

    #[Route('memberships/add-credits/{membership}/{amount}')]
    public function addCredits(ManagerRegistry $doctrine, $membership, $amount)
    {
        $membership = $doctrine->getRepository(Membership::class)->find($membership);

        if (!$membership) {
            return $this->json("Membership doesnt exist!");
        }

        if (!$membership->isIsActive()) {
            return $this->json("Membership has been canceled", 401);
        }

        $membership->setCredits($membership->getCredits()+$amount);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($membership);
        $entityManager->flush();

        return $this->json("Current credits: ".$membership->getCredits());
    }

    #[Route('memberships/subtract-credits/{membership}/{amount}')]
    public function subtractCredits(ManagerRegistry $doctrine, $membership, $amount)
    {
        $membership = $doctrine->getRepository(Membership::class)->find($membership);

        if (!$membership) {
            return $this->json("Membership doesnt exist!");
        }

        if (!$membership->isIsActive()) {
            return $this->json("Membership has been canceled", 401);
        }

        $membership->setCredits($membership->getCredits()-$amount);

        if (intval($amount) > $membership->getCredits()) {
            return $this->json("Not enough credits");
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($membership);
        $entityManager->flush();

        return $this->json("Current credits: ".$membership->getCredits());
    }

    #[Route('/memberships', methods: "GET")]
    public function getAllmemberships(ManagerRegistry $doctrine)
    {
        $memberships = $doctrine->getRepository(membership::class)->findAll();

        $data = [];
        foreach ($memberships as $membership) {
            $data[] = [
                "id" => $membership->getId(),
                "start-date" => $membership->getStartDate(),
                "end-date" => $membership->getEndDate(),
                "user_id" => $membership->getUserId()->getId(),
                "is-active" => $membership->isIsActive(),
            ];
        }

        return $this->json($data);
    }

    #[Route('memberships/{membership}')]
    public function getMembership(ManagerRegistry $doctrine, $membership)
    {
        $membership = $doctrine->getRepository(Membership::class)->find($membership);

        if (!$membership) {
            return $this->json("Membership doesnt exist!");
        }

        return $this->json([
            "id" => $membership->getId(),
            "start-date" => $membership->getStartDate(),
            "end-date" => $membership->getEndDate(),
            "user_id" => $membership->getUserId()->getId(),
            "is-active" => $membership->isIsActive(),
        ]);
    }
}
