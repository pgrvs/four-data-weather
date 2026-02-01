<?php

namespace App\Controller;

use App\Entity\Search;
use App\Repository\SearchRepository;
use App\Service\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/searches', name: 'api_search_')]
final class SearchController extends AbstractController
{
    /**
     * GET /api/searches
     * Récupère l'historique des recherches.
     */
    #[Route('', methods: ['GET'])]
    public function index(SearchRepository $searchRepository, ApiResponse $response): JsonResponse
    {
        $searches = $searchRepository->findBy([], ['createdAt' => 'DESC']);

        return $response->success($searches);
    }

    /**
     * POST /api/searches
     * Sauvegarde une ville en favori.
     */
    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, ApiResponse $response, SearchRepository $searchRepository): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $response->error('Format JSON invalide', 400);
        }

        $cityName = (string) ($data['city'] ?? '');
        $lat = (float) ($data['latitude'] ?? 0.0);
        $lon = (float) ($data['longitude'] ?? 0.0);

        $existingSearch = $searchRepository->findOneBy([
            'city' => $cityName,
            'latitude' => $lat,
            'longitude' => $lon
        ]);

        if ($existingSearch) {
            return $response->error('Cette ville est déjà dans vos favoris.', 409);
        }

        $search = new Search();
        $search->setCity($cityName);
        $search->setLatitude($lat);
        $search->setLongitude($lon);

        $errors = $validator->validate($search);
        if (count($errors) > 0) {
            return $response->error((string) $errors, 400);
        }

        $em->persist($search);
        $em->flush();

        return $response->success($search, 'Recherche sauvegardée avec succès', 201);
    }

    /**
     * DELETE /api/searches/{id}
     * Supprime un favori.
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(?Search $search, EntityManagerInterface $em, ApiResponse $response): JsonResponse {
        if (!$search) {
            return $response->error('Recherche introuvable', 404);
        }

        $em->remove($search);
        $em->flush();

        return $response->success(null, 'Suppression effectuée');
    }
}
