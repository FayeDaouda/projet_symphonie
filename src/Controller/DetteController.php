<?php
// src/Controller/DetteController.php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Dette;
use App\Form\DetteType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DetteController extends AbstractController
{
    #[Route('/clients/{clientId}/dettes', name: 'client_dettes')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator, int $clientId): Response
    {
        // Récupérer le client par son ID
        $client = $entityManager->getRepository(Client::class)->find($clientId);
        
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        // Créer une requête pour récupérer les dettes du client
        $queryBuilder = $entityManager->getRepository(Dette::class)->createQueryBuilder('d')
            ->where('d.client = :client')
            ->setParameter('client', $client);

        $query = $queryBuilder->getQuery();

        // Paginer les résultats
        $dettes = $paginator->paginate(
            $query, // La requête
            $request->query->getInt('page', 1), // Numéro de la page (par défaut 1)
            6 // Nombre d'éléments par page
        );

        return $this->render('dette/index.html.twig', [
            'client' => $client,
            'dettes' => $dettes,
        ]);
    }

    #[Route('/clients/{clientId}/dettes/ajouter', name: 'dette_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, int $clientId): Response
    {
        // Récupérer le client par son ID
        $client = $entityManager->getRepository(Client::class)->find($clientId);
        
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        $dette = new Dette();
        $form = $this->createForm(DetteType::class, $dette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dette->setClient($client); // Associer la dette au client
            $entityManager->persist($dette);
            $entityManager->flush();

            // Rediriger vers la liste des dettes du client
            return $this->redirectToRoute('client_dettes', ['clientId' => $clientId]);
        }

        return $this->render('dette/add.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }
}
