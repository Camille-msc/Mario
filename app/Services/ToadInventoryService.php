<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadInventoryService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getInventoriesByFilmId(int $filmId): ?array
    {
        $url     = $this->baseUrl . '/inventories/available/film/' . $filmId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Inventories', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getInventoryById(int $inventoryId): ?array
    {
        $url     = $this->baseUrl . '/inventories/' . $inventoryId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createInventory(array $data): ?array
    {
        $url     = $this->baseUrl . '/inventories';
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Création inventory KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function updateInventory(int $inventoryId, array $data): ?array
    {
        $url     = $this->baseUrl . '/inventories/' . $inventoryId;
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Mise à jour inventory KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteInventory(int $inventoryId): bool
    {
        $url     = $this->baseUrl . '/inventories/' . $inventoryId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->delete($url);

            if ($response->successful() || $response->status() === 204) {
                return true;
            }

            Log::warning('Suppression inventory KO', ['status' => $response->status()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression inventory', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function checkInventoryAvailability(int $inventoryId): ?array
    {
        $url     = $this->baseUrl . '/inventories/checkIfDVDIsAvailable/' . $inventoryId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            if ($response->successful()) {
                // Toad retourne un boolean, on l'enveloppe dans un tableau pour un accès uniforme côté contrôleur
                return ['available' => $response->json()];
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur disponibilité inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // récupère l'historique de locations d'un DVD - nécessaire avant suppression à cause de la contrainte ON DELETE RESTRICT de Peach
    public function getRentalsByInventoryId(int $inventoryId): ?array
    {
        $url     = $this->baseUrl . '/inventories/' . $inventoryId . '/rentals';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur rentals par inventory', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteRental(int $rentalId): bool
    {
        $url     = $this->baseUrl . '/rentals/' . $rentalId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->delete($url);
            return $response->successful() || $response->status() === 204;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression rental (depuis inventory)', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function getAllStores(): ?array
    {
        $url     = $this->baseUrl . '/stores';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Stores', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    private function getUserToken(): ?string
    {
        return session('toad_user')['token'] ?? null;
    }
}
