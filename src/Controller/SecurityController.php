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
use Symfony\Component\Security\Core\User\UserInterface;


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


            $user->addRole("ROLE_USER");


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
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder) {

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);

            $manager = $this->getDoctrine()->getManager();

            $user->addRole("ROLE_USER");

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/mdp-change", name="security_password_change")
     */
    public function passwordChange(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {

        $old_pwd = $request->get('old_password');
        $new_pwd = $request->get('new_password');
        $new_pwd_confirm = $request->get('new_password_confirm');


        $user = $this->getUser();
        $checkPass = $passwordEncoder->isPasswordValid($user, $old_pwd);

        if( $new_pwd ===  $new_pwd_confirm){
            if($checkPass === true) {
                $new_pwd_encoded = $passwordEncoder->encodePassword($user, $new_pwd_confirm);
                $user->setPassword($new_pwd_encoded);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('notice', 'Le mot de passe a eté bien changé !');
                    
            }else {
                if (!is_null($old_pwd)) {
                    $this->addFlash('notice', 'L\'ancien mot de passe n\'est pas correct !');
                }
            }
        }else{
            if (!is_null($new_pwd) && !is_null($new_pwd_confirm)) {
                $this->addFlash('notice', 'Les deux mots de passe ne correspondent pas');
            }
        }

        return $this->render('security/changePassword.html.twig', [
            'form' => '',
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

        if ($user->getEmail() == $data['email'] && $encoder->isPasswordValid($user, $data['password'], $user->getSalt())) {

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
            'error' => 'failure',
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
    public function signout()
    {
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

    /**
     * @Route("/flutter/profile/modify", name="modifyProfile")
     */
    public function modifyProfile(
        Request $request,
        UserInterface $user,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ): Response {
        $token = $request->headers->get('X-AUTH-TOKEN');

        $token = $this->getDoctrine()->getRepository('App:ApiToken')->findOneBy(['token' => $token]);
        $now = new \DateTime();
        if ($token !== null && $token->getExpiresAt() > $now) {
            $user = $token->getUser();

            $data = json_decode($request->getContent(), true);

            $user->setUsername($data["username"]);
            $user->setEmail($data["email"]);
            if ($data['password'] != null) {
                $change = true;
                $user->setPassword($data["password"]);
            } else {
                $change = false;
            }

            // password must be 10 characters long, with at least an uppercase, a lowercase and one number
            if ($user->getUsername() == null || $user->getUsername() == '') {
                $message = 'Nom invalide. Veuillez entrer un nom.';
            } else if ($user->getEmail() == null || $user->getEmail() == '' || filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) === false) {
                $message = 'Email invalide. Veuillez entrer un email valide.';
            } else if ($change && ($user->getPassword() == null || $user->getPassword() == '' || filter_var($user->getPassword(), FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{10,}$/"))) === false)) {
                $message = 'Mot de passe invalide. Veuillez entrer un mot de passe de 10 caractères minimum, avec au moins une minuscule, une majuscule et un chiffre';
            } else if ($user->getNom() == null || $user->getNom() == '') {
                $message = 'Nom invalide. Veuillez entrer votre nom.';
            } else {
                $hash = $encoder->encodePassword($user, $user->getPassword());

                if($change) {
                    $user->setPassword($hash);
                }

                $manager = $this->getDoctrine()->getManager();

                $manager->flush();

                return $this->json([
                    'message' => 'Modification réussie',
                    'success' => true,
                ]);
            }
            return $this->json([
                'message' => $message,
                'success' => false,
            ]);
        }
    }



    /**
     * @Route("admin/inscription", name="newAdmin")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) {

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $user->addRole("ROLE_ADMIN");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte à bien été enregistré.');

        }
        return $this->render('back_office/register-admin.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/flutter/password", name="changePassword")
     */
    public function changePassword(
        Request $request,
        UserInterface $user,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder
    ): Response {
        $token = $request->headers->get('X-AUTH-TOKEN');

        $token = $this->getDoctrine()->getRepository('App:ApiToken')->findOneBy(['token' => $token]);
        $now = new \DateTime();
        if ($token !== null && $token->getExpiresAt() > $now) {
            $user = $token->getUser();
            $data = json_decode($request->getContent(), true);

            if ($encoder->isPasswordValid($user, $data['password'], $user->getSalt())) {
                return $this->json([
                    'password' => 'is valid',
                ]);
            } else {
                return $this->json([
                    'password' => 'invalid',
                ]);
            }
        } else {
            return $this->json([
                'result' => 'failure'
            ]);
        }
    }

}
