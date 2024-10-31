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
        $client = $entityManager->getRepository(Client::class)->find($clientId);
        
        if (!$client) {
            throw $this->createNotFoundException('Client non trouvé.');
        }

        $queryBuilder = $entityManager->getRepository(Dette::class)->createQueryBuilder('d')
            ->where('d.client = :client')
            ->setParameter('client', $client);

        $query = $queryBuilder->getQuery();

        $dettes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('client/dettes.html.twig', [ // Chemin mis à jour
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
    
            // Calculer montantRestant
            $montantRestant = $dette->getMontant() - ($dette->getMontantVerser() ?? 0);
            
            // Mettre à jour le statut selon montantRestant
            if (abs($montantRestant) < 0.01) { // Permet une petite marge d'erreur
                $dette->setStatut('solder');
            } else {
                $dette->setStatut('non_solde');
            }
    
            $entityManager->persist($dette);
            $entityManager->flush();
    
            // Ajouter un message flash de succès
            $this->addFlash('success', 'La dette a été ajoutée avec succès.');
    
            // Rediriger vers la liste des dettes du client
            return $this->redirectToRoute('client_dettes', ['clientId' => $clientId]);
        }
    
        // Renvoyer le template avec le formulaire
        return $this->render('dette/add.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }
    

}
