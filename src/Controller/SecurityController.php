<?php

namespace App\Controller;



use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, 
     UserPasswordEncoderInterface $encoder) 
    {

    $user = new User();
        //($user);
    $form = $this->createForm(RegistrationType::class, $user);

    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
        $hash = $encoder->encodePassword($user, $user->getPassword());

        $user->setPassword($hash);

        $manager = $this->getDoctrine()->getManager();

        
        $user = $user->setRole("user");


        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('security_login');
    }

    return $this->render('security/registration.html.twig' , [
    'form' => $form->createView()
    ]);
    }
    /**
     * @Route("/connexion",name="security_login")
     */
    public function login() {
        return $this->render('security/login.html.twig');    }
      
     
    /**
     * @Route("/logout",name="security_logout")
     */
    public function logout() {

        return $this->render('security/logout.html.twig');    } 
    
}   