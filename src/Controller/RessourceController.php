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
         * @Route("/ressources", name="ressources")
        */
        public function listRessources() : Response
        {
            $repository = $this->getDoctrine()->getRepository(Ressource::class);
            //$ressources = $repository->findAll();
            $ressources = $repository->findBy(
                ['statut' => 'publie'],
                ['id' => 'DESC']
            );

            foreach ($ressources as $key => $ressource){
                $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
            }


            return $this->render('ressource/ressources.html.twig', [
                'ressources' => $ressources,
                'extension' => $ext,

            ]);
            }

        /**
         * @Route("/ressource/delete/{id}", name="deleteRessource")
        */
        public function deleteRessource(Request $request, EntityManagerInterface $manager, Ressource $ressource)
        {
            $manager->remove($ressource);
            if ( $manager->flush()) {
                $this->addFlash(
                    'notice',
                    'Le post a eté bien supprimé !'
                );
                };

            return $this->redirectToRoute("ressources");

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
                    $oldfile = $this->getParameter('medias_directory').'/'. $ressource->getMedia();
                    //dd($oldfile);
                    if (file_exists($oldfile)) {
                        unlink($oldfile); //ici je supprime le fichier
                    }

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
                
                return $this->redirectToRoute("ressources");
    
    
            }
    
            return $this->render('ressource/addRessource.html.twig', [
                'form' => $form->createView(),
            ]);


            }

        /**
         * @Route("/ressource/view/{id}", name="viewRessource")
        */
        public function viewRessource(Request $request, EntityManagerInterface $manager, Ressource $ressource): Response
        {

                $ext = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);

            return $this->render('ressource/viewRessource.html.twig', [
                'ressource' => $ressource,
                'ext' => $ext,

            ]);


            }

}
