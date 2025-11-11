<?php

namespace App\Controller;

use App\Service\StorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/chats', name: 'api_chats_')]
class ChatListAPIController extends AbstractController
{
    public function __construct(
        private StorageService $storageService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $chats = $this->storageService->getAllChats();

            return $this->json([
                'success' => true,
                'data' => $chats,
                'count' => count($chats)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Chyba pri načítaní chatov: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['title']) || empty($data['title'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pole "title" je povinné'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!isset($data['messages']) || !is_array($data['messages'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pole "messages" je povinné a musí byť pole'
                ], Response::HTTP_BAD_REQUEST);
            }

            $chat = $this->storageService->saveChat(
                $data['title'],
                $data['messages']
            );

            return $this->json([
                'success' => true,
                'data' => $chat,
                'message' => 'Chat bol úspešne uložený'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Chyba pri ukladaní chatu: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $deleted = $this->storageService->deleteChat($id);

            if ($deleted) {
                return $this->json([
                    'success' => true,
                    'message' => 'Chat bol úspešne zmazaný'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Chat s ID ' . $id . ' neexistuje'
                ], Response::HTTP_NOT_FOUND);
            }

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Chyba pri mazaní chatu: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}