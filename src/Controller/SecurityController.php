<?php

namespace App\Controller;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\User;

class SecurityController extends Controller
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(User $user) 
    {
     

     $form = $this->createForm(RegistrationType::class, $user);

     return $this->render('security/registration.html.twig' , [
'form' => $form->createView()
     ]);
    }
}
