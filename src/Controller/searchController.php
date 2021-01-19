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
            if (!empty($ressources)){

                foreach ($ressources as $key => $ressource){
                    $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
                }
    
            }else{
                $ext='';
            }

        }
        if(!isset($ext)){
            $ext='';
        }
        return $this->render('search/searchRessource.html.twig', [
            'form' => $form->createView(),
            'ressources' => $ressources,
            'extension' => $ext,
            'crud' => false,

        ]);

    }

    /**
     * @Route("/ressource/search->", name="navSearch")
     */
    public function navSearchRessource(Request $request, EntityManagerInterface $manager, RessourceRepository $ressourceRepository): Response
    {
        $keyword = $_GET['keyword'];
        $ressources = '';

		if (isset($keyword) && !empty($keyword)) {

            $ressources = $ressourceRepository->navSearchRessourceRep($keyword);
            if (!empty($ressources)){

                foreach ($ressources as $key => $ressource){
                    $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
                }
    
            }else{
                $ext='';
            }
    
        

        return $this->render('search/NavSearchRessource.html.twig', [
            'ressources' => $ressources,
            'extension' => $ext,
            'crud' => false,


        ]);
        }else{
            return $this->redirectToRoute("ressources");

        }
    }


}
