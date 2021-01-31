<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ressource;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;

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
    
    /**
    * @Route("/admin/ressources", name="adminRessources")
    */
    public function Ressources(): Response
    {

        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        $ressources = $repository->findAll();

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();


        return $this->render('back_office/ressources.html.twig', [
            'ressources' => $ressources,
            'users' => $users,
        ]);
    }
    
    /**
    * @Route("/admin/ressource/delete/{id}", name="RessourceDelete")
    */
    public function ressourceDelete(EntityManagerInterface $manager, Ressource $ressource): Response
    {
        $manager->remove($ressource);

        if ( $manager->flush()) {

            $this->addFlash('notice', 'La ressource a eté bien supprimé !');
            };

        return $this->redirectToRoute("adminRessources");
    } 
    
    /**
    * @Route("/admin/ressource/suspend/{id}", name="RessourceSuspend")
    */
    public function RessourceSuspend(EntityManagerInterface $manager, Ressource $ressource): Response
    {
        $ressource->setStatut("suspendu");
        $manager->persist($ressource);

        if ( $manager->flush()) {

            $this->addFlash('notice', 'La ressource a eté bien suspendue !');
            };

        return $this->redirectToRoute("adminRessources");
    }  
    
    /**
    * @Route("/admin/ressource/publish/{id}", name="RessourcePublish")
    */
    public function RessourcePublish(EntityManagerInterface $manager, Ressource $ressource): Response
    {
        $ressource->setStatut("publie");
        $manager->persist($ressource);

        if ( $manager->flush()) {

            $this->addFlash('notice', 'La ressource a eté bien publié !');
            };

        return $this->redirectToRoute("adminRessources");
    }
    
    /**
    * @Route("/admin/statistics", name="Statistics")
    */
    public function Statistics(EntityManagerInterface $manager, Ressource $ressource ): Response
    {

        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        $ressources = $repository->findAll();

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        $repository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $repository->findAll();

        return $this->render('back_office/statistics.html.twig', [
            'ressources' => $ressources,
            'comments' => $comments,
            'users' => $users,
        ]);
    }
}
