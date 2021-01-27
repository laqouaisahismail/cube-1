<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    /**
     * @Route("/admin", name="adminHome")
     */
    public function index(): Response
    {

        return $this->render('back_office/index.html.twig', []);
    } 

    /**
    * @Route("/admin/users", name="adminUsers")
    */
    public function Users(): Response
    {

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        return $this->render('back_office/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    /**
    * @Route("/admin/user/delete/{id}", name="UserDelete")
    */
    public function userDelete(EntityManagerInterface $manager, User $user): Response
    {
        $manager->remove($user);

        if ( $manager->flush()) {

            $this->addFlash('notice', 'L\'utilisateur a eté bien supprimé !');
            };

        return $this->redirectToRoute("adminUsers");
    }
}
