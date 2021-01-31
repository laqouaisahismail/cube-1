<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class SecurityController extends AbstractController
{

    /**
     * @Route("/flutter/signup", name="security_signup", methods={"POST"})
     */
    public function signup(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ) {

        $user = new User();
        $data = json_decode($request->getContent(), true);
        $user->setUsername($data["username"]);
        $user->setEmail($data["email"]);
        $user->setPassword($data["password"]);
        $user->setNom($data["nom"]);
        // password must be 10 characters long, with at least an uppercase, a lowercase and one number
        if ($user->getUsername() == null || $user->getUsername() == '') {
            $message = 'Nom invalide. Veuillez entrer un nom.';
        } else if ($user->getEmail() == null || $user->getEmail() == '' || filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
            $message = 'Email invalide. Veuillez entrer un email valide.';
        } else if ($user->getPassword() == null || $user->getPassword() == '' || filter_var($user->getPassword(), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{10,}$/"))) === false) {
            $message = 'Mot de passe invalide. Veuillez entrer un mot de passe de 10 caractères minimum, avec au moins une minuscule, une majuscule et un chiffre';
        } else if ($user->getNom() == null || $user->getNom() == '') {
            $message = 'Nom invalide. Veuillez entrer votre nom.';
        } else {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);

            $manager = $this->getDoctrine()->getManager();


            $user = $user->setRole("user");


            $manager->persist($user);
            $manager->flush();

            return $this->json([
                'message' => 'Inscription réussie',
                'success' => true,
            ]);
        }
        return $this->json([
            'message' => $message,
            'success' => false,
        ]);
    }


    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ) {

        $user = new User();
        //($user);
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);

            $manager = $this->getDoctrine()->getManager();


            $user = $user->setRole("user");


            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/flutter/signin", name="security_signin", methods={"POST"})
     */
    public function signin(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
 
        $data = json_decode($request->getContent(), true);

        //$user = $this->getUser();
        $user = $data['email'];
        $pass = $data['password'];

        $user = $this->getDoctrine()->getRepository('App:User')->findOneBy(['email' => $user]);
        if ($user === null) {
            return $this->json([
                'login' => 'failure',
                'user' => 'null',
            ]);
        }

        if($user->getEmail() == $data['email'] && $encoder->isPasswordValid($user, $data['password'], $user->getSalt())) {

            $apiToken = new ApiToken($user);
            $manager->persist($apiToken);
            $manager->flush();

            return $this->json([
                'login' => 'successful',
                'username' => $user->getUsername(),
                'token' => $apiToken->getToken(),
            ]); 
        }

        return $this->json([
            'login' => 'failure',
        ]);
    }

    /**
     * @Route("/flutter/profile", name="getProfile", methods={"POST"})
     */
    public function getProfile(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
 
        $token = $request->headers->get('X-AUTH-TOKEN');
        echo $token;
        $token = $this->getDoctrine()->getRepository('App:ApiToken')->findOneBy(['token' => $token]);
        $now = new \DateTime();
        if ($token !== null && $token->getExpiresAt() > $now) {

            return $this->json([
                'operation' => 'success',
                'username' => $token->getUser()->getUsername(),
                'email' => $token->getUser()->getEmail(),
                'name' => $token->getUser()->getNom(),
            ]); 
        }

        return $this->json([
            'operation' => 'failure',
        ]);
    }



    /**
     * @Route("/connexion",name="security_login")
     */
    public function login()
    {
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/flutter/signout", name="signout")
     */
    public function signout() {
        return $this->json([
            'logout' => 'success'
        ]);
    }


    /**
     * @Route("/logout",name="security_logout")
     */
    public function logout()
    {

        return $this->render('security/logout.html.twig');
    }
}
