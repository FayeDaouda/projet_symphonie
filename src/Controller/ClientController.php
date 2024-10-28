<?php
// src/Controller/ClientController.php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface; // Importation du pagineur
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    // Méthode pour afficher la liste des clients
    #[Route('/clients', name: 'client_index')]
    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les paramètres de filtrage
        $surnameFilter = $request->query->get('surname');
        $telephoneFilter = $request->query->get('telephone');
    
        // Création de la requête pour récupérer les clients
        $queryBuilder = $entityManager->getRepository(Client::class)->createQueryBuilder('c');
    
        // Appliquer les filtres
        if ($surnameFilter) {
            $queryBuilder->andWhere('c.surname LIKE :surname')
                         ->setParameter('surname', '%' . $surnameFilter . '%');
        }
        if ($telephoneFilter) {
            $queryBuilder->andWhere('c.telephone LIKE :telephone')
                         ->setParameter('telephone', '%' . $telephoneFilter . '%');
        }
    
        $clientsQuery = $queryBuilder->getQuery();
    
        // Pagination
        $clients = $paginator->paginate(
            $clientsQuery,
            $request->query->getInt('page', 1),
            6 // Limite par page
        );
    
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'surnameFilter' => $surnameFilter,
            'telephoneFilter' => $telephoneFilter,
        ]);
    }

    // Méthode pour afficher le formulaire d'ajout d'un client
    #[Route('/clients/add', name: 'client_create')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $client = new Client();
            $client->setSurname($request->request->get('surname'));
            $client->setTelephone($request->request->get('telephone'));
            $client->setAdresse($request->request->get('adresse'));

            $entityManager->persist($client);
            $entityManager->flush();

            // Redirection après l'ajout
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/add.html.twig');
    }

    // Méthode pour afficher les détails des dettes d'un client
    #[Route('/clients/{id}/dettes', name: 'client_dettes')]
    public function showDettes(EntityManagerInterface $entityManager, int $id): Response
    {
        // Récupérer le client par son ID
        $client = $entityManager->getRepository(Client::class)->find($id);

        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        $dettes = $client->getDettes(); // Récupérer les dettes du client

        return $this->render('client/dettes.html.twig', [
            'client' => $client,
            'dettes' => $dettes,
        ]);
    }
}
