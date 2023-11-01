<?php

namespace src\Controller;

use src\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route("users/create", methods: "POST")]
    public function createUser(ManagerRegistry $doctrine, Request $request)
    {
        $data = $request->toArray();

        if ($data != []){

        if (!isset($data["email"])){
            return $this->json("Enter an email address");
        }

        $user = new User();
        $user->setEmail($data["email"]);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user->getId());

        }

    }

    #[Route("users/update/{id}", methods: "POST")]
    public function updateUser(ManagerRegistry $doctrine, Request $request, $id)
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json("User doesn't exist");
        }

        $data = $request->toArray();

        if (!isset($data["email"])){
            return ($this->json("Please enter an email address"));
        }

        $user->setEmail($data["email"]);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json("user updated. new email: ".$user->getEmail());

    }

    #[Route("users/delete/{id}")]
    public function deleteUser(ManagerRegistry $doctrine, $id)
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json("user not found");
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json("deleted user");
    }

    #[Route('users/{id}')]
    public function findUser(ManagerRegistry $doctrine, $id)
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json("user not found");
        }

        return $this->json(["email" => $user->getEmail(), "membership" => $user->getMembership()->getId()]);
    }

    #[Route('/users', methods: "GET")]
    public function getAllUsers(ManagerRegistry $doctrine)
    {
        $users = $doctrine->getRepository(user::class)->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                "email" => $user->getEmail(),
            ];
        }

        return $this->json($data);
    }
}
