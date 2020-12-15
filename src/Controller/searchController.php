<?php


namespace App\Controller;

use App\Form\SearchRessourceType;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class searchController extends AbstractController
{
    /**
     * @Route("/ressource/search", name="search")
     */
    public function searchRessource(Request $request, EntityManagerInterface $manager, RessourceRepository $ressourceRepository): Response
    {

        $form = $this->createForm(SearchRessourceType::class);

        $form->handleRequest($request);

        $ressources = '';

		if ($form->isSubmitted() && $form->isValid()) {

            $criteria = $form->getData();
            $ressources = $ressourceRepository->searchRessourceRep($criteria);
            //dd($criteria);

        }

        return $this->render('search/searchRessource.html.twig', [
            'form' => $form->createView(),
            'ressources' => $ressources,
        ]);

    }
}
