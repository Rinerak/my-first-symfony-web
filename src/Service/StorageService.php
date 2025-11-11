<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

class StorageService
{
    private string $storageDir;
    private Filesystem $filesystem;

    public function __construct(string $projectDir = "")
    {
        $this->storageDir = $projectDir . '/var/chat_storage';
        $this->filesystem = new Filesystem();

        if (!$this->filesystem->exists($this->storageDir)) {
            $this->filesystem->mkdir($this->storageDir);
        }
    }

    public function getAllChats(): array
    {
        $chats = [];
        $files = glob($this->storageDir . '/chat_*.json');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $chat = json_decode($content, true);
            
            if ($chat) {
                $chats[] = $chat;
            }
        }

        usort($chats, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $chats;
    }

    public function saveChat(string $title, array $messages): array
    {
        $id = $this->generateNewId();

        $chat = [
            'id' => $id,
            'title' => $title,
            'messages' => $messages,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $filename = $this->storageDir . '/chat_' . $id . '.json';
        file_put_contents($filename, json_encode($chat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $chat;
    }

    public function deleteChat(int $id): bool
    {
        $filename = $this->storageDir . '/chat_' . $id . '.json';

        if ($this->filesystem->exists($filename)) {
            $this->filesystem->remove($filename);
            return true;
        }

        return false;
    }

    private function generateNewId(): int
    {
        $chats = $this->getAllChats();

        if (empty($chats)) {
            return 1;
        }

        $maxId = max(array_column($chats, 'id'));
        return $maxId + 1;
    }
}