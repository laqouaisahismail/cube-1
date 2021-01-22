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
use Symfony\Component\Security\Core\User\UserInterface;

class RessourceController extends AbstractController
{/**
     * @Route("/resources")
     */
    public function listResources(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        $ressources = $repository->findAll();

        $ressourceJson = [];
        foreach ($ressources as $key => $ressource) {
            $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
        }
        foreach($ressources as $key => $ress) {
            array_push($ressourceJson, $ress->jsonSerialize());
        }
        $response = new Response();
        $response->setContent(json_encode($ressourceJson));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    
    /**
     * @Route("/resources/{id}")
     */
    public function ApiSearchById(Request $request, EntityManagerInterface $manager, Ressource $ressource): Response
    {
        $response = new Response();
        $ressource2 = $ressource->jsonSerialize();
        $response->setContent(json_encode($ressource2));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/resources", name="searchByTitle")
     */
    public function listResourcesByTitle(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        //$ressources = $repository->findAll();
        $ressources = $repository->findBy(
            ['titre' => $_GET['name']],
            ['id' => 'DESC']
        );

        $ressourceJson = [];
        foreach ($ressources as $key => $ressource) {
            $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
        }
        foreach($ressources as $key => $ress) {
            array_push($ressourceJson, $ress->jsonSerialize());
        }
        $response = new Response();
        $response->setContent(json_encode($ressourceJson));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/resourcestimeline", name="searchByDate")
     */
    public function listResourcesByDate(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        //$ressources = $repository->findAll();
        $ressources = $repository->findBy(
            ['statut' => 'publie'],
            ['date' => 'DESC']
        );

        $ressourceJson = [];
        foreach ($ressources as $key => $ressource) {
            $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
        }
        foreach($ressources as $key => $ress) {
            array_push($ressourceJson, $ress->jsonSerialize());
        }
        $response = new Response();
        $response->setContent(json_encode($ressourceJson));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        
        return $response;
    }

    /**
     * @Route("/resources", name="searchByiduser")
     */
    public function listResourcesByIduser(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        //$ressources = $repository->findAll();
        $ressources = $repository->findBy(
            ['iduser' => $_GET['name']],
            ['id' => 'DESC']
        );

        $ressourceJson = [];
        foreach ($ressources as $key => $ressource) {
            $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
        }
        foreach($ressources as $key => $ress) {
            array_push($ressourceJson, $ress->jsonSerialize());
        }
        $response = new Response();
        $response->setContent(json_encode($ressourceJson));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/resources", name="searchByCategory")
     */
    public function listResourcesByCategory(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ressource::class);
        //$ressources = $repository->findAll();
        $ressources = $repository->findBy(
            ['category' => $_GET['name']],
            ['id' => 'DESC']
        );

        $ressourceJson = [];
        foreach ($ressources as $key => $ressource) {
            $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
        }
        foreach($ressources as $key => $ress) {
            array_push($ressourceJson, $ress->jsonSerialize());
        }
        $response = new Response();
        $response->setContent(json_encode($ressourceJson));
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("profile/ressource/ajout", name="addRessource")
     */
    public function AddRessource(Request $request, EntityManagerInterface $manager, UserInterface $user): Response
    {

        $userId = $user->getId();
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
            $ressource->setIduser($userId);
            
			$manager->persist($ressource);
            $manager->flush();

            $this->addFlash(
                'notice',
                'Le post a eté bien publié !'
            );
            
            return $this->redirectToRoute("myResources");


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

            if(!empty($ressources)){
                foreach ($ressources as $key => $ressource){
                    $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
                }
    
            }else{
                $ext = "Pas de ressource";
            }


            return $this->render('ressource/ressources.html.twig', [
                'ressources' => $ressources,
                'extension' => $ext,
                'crud' => false,


            ]);
            }

        /**
         * @Route("/profile/ressources", name="myResources")
        */
        public function listMyRessources(UserInterface $user) : Response
        {
            $repository = $this->getDoctrine()->getRepository(Ressource::class);
            //$ressources = $repository->findAll();

            $ressources = $repository->findBy(
                ['iduser' => $user->getId()],
                ['id' => 'DESC']
            );

            if(!empty($ressources)){
                foreach ($ressources as $key => $ressource){
                    $ext[$ressource->getId()] = pathinfo($ressource->getMedia(), PATHINFO_EXTENSION);
                }
    
            }else{
                $ext= "Pas de ressource";
            }


            return $this->render('ressource/ressources.html.twig', [
                'ressources' => $ressources,
                'extension' => $ext,
                'crud' => true,

            ]);
            }

        /**
         * @Route("profile/ressource/delete/{id}", name="deleteRessource")
        */
        public function deleteRessource(Request $request, EntityManagerInterface $manager, Ressource $ressource, UserInterface $user)
        {
            if ($user->getId() == $ressource->getIduser()){
                $manager->remove($ressource);

                if ( $manager->flush()) {

                    $this->addFlash('notice', 'La ressource a eté bien supprimé !');

                    };
    

            }else{
                if ( $manager->flush()) {
                    $this->addFlash('notice','Vous ne pouvez supprimer que vos ressources !');
                    };

            }
            return $this->redirectToRoute("myResources");

        }

        /**
         * @Route("profile/ressource/edit/{id}", name="editRessource")
        */
        public function editRessources(Request $request, EntityManagerInterface $manager, Ressource $ressource, UserInterface $user): Response
        {

            $form = $this->createForm(RessourceType::class, $ressource);
            $form->handleRequest($request);
    
            if ($user->getId() == $ressource->getIduser()){
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
                    
                    return $this->redirectToRoute("myResources");

        
                }

            }else{
                $this->addFlash(
                    'notice',
                    'Vous ne pouvez modifier que vos ressources !'
                );
                return $this->redirectToRoute("myResources");
    
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

        /**
         * @Route("/profile", name="profile")
        */
        public function Profile(UserInterface $user): Response
        {

            return $this->render('profile.html.twig', [
                'user' => $user,

            ]);


            }

}
