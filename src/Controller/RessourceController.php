<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RessourceController extends AbstractController
{
    /**
     * @Route("/ressource/ajout", name="addRessource")
     */
    public function AddRessource(Request $request, EntityManagerInterface $manager): Response
    {


        $ressource = new Ressource();
        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()) {




            /** @var UploadedFile $ressourceFile */
            $ressourceFile = $form->get('media')->getData();

            // this condition is needed because the 'media' field is not required
            // so the file must be processed only when a file is uploaded
            if ($ressourceFile) {
                $originalFilename = pathinfo($ressourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$ressourceFile->guessExtension();

                // Move the file to the directory where medias are stored
                try {
                    $ressourceFile->move(
                        $this->getParameter('medias_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'ressourceFilename' property to store the file name
                // instead of its contents
                $ressource->setMedia($newFilename);
            }

            // ... persist the $ressource variable or any other work




            $ressource->setDate(new \DateTime());
			$manager->persist($ressource);
            $manager->flush();

            $this->addFlash(
                'notice',
                'Le post a eté bien publié !'
            );
            
            return $this->redirectToRoute("addRessource");


        }

        return $this->render('ressource/addRessource.html.twig', [
            'form' => $form->createView(),
        ]);
    }

        /**
         * @Route("/", name="home")
        */
        public function listRessources() : Response
        {
            $repository = $this->getDoctrine()->getRepository(Ressource::class);
            $ressources = $repository->findAll();

            foreach ($ressources as $key => $ressource){

                $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);

            }


            return $this->render('index.html.twig', [
                'ressources' => $ressources,
                'extension' => $ext,

            ]);
            }

        /**
         * @Route("/ressource/edit/{id}", name="editRessource")
        */
        public function editRessources(Request $request, EntityManagerInterface $manager, Ressource $ressource): Response
        {

            $form = $this->createForm(RessourceType::class, $ressource);
            $form->handleRequest($request);
    
    
            if ($form->isSubmitted() && $form->isValid()) {    
        
                /** @var UploadedFile $ressourceFile */
                $ressourceFile = $form->get('media')->getData();

                // this condition is needed because the 'media' field is not required
                // so the file must be processed only when a file is uploaded
                if ($ressourceFile) {
                    unlink($this->getParameter('medias_directory').'/'. $ressource->getMedia()); //ici je supprime le fichier

                    $originalFilename = pathinfo($ressourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$ressourceFile->guessExtension();

                    // Move the file to the directory where medias are stored
                    try {
                        $ressourceFile->move(
                            $this->getParameter('medias_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash(
                            'notice',
                            'Le media est pas telechargé !'
                        );
                            }

                    // updates the 'ressourceFilename' property to store the file name
                    // instead of its contents
                    $ressource->setMedia($newFilename);
                }


                $manager->flush();
    
                $this->addFlash(
                    'notice',
                    'Le post a eté bien modifié !'
                );
                
                return $this->redirectToRoute("home");
    
    
            }
    
            return $this->render('ressource/addRessource.html.twig', [
                'form' => $form->createView(),
            ]);
                    

            }

}
