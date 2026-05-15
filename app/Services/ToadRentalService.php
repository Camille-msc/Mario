<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadRentalService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllRentals(int $limit = 20, int $offset = 0): ?array
    {
        // /rentals/all et non /rentals - l'endpoint spécifique de Toad qui exclut les locations "dans le panier" (statusId=2)
        $url     = $this->baseUrl . '/rentals/all';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url, ['limit' => $limit, 'offset' => $offset]);

            if ($response->successful()) {
                $data = $response->json();
                // Toad retourne parfois {content: [...]} (pagination Spring), parfois un tableau direct
                return $data['content'] ?? $data;
            }

            Log::warning('Rentals API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Rentals', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getRentalsCount(): int
    {
        $url     = $this->baseUrl . '/rentals/all/count';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);
            return $response->successful() ? (int) $response->body() : 0;
        } catch (\Throwable $e) {
            Log::error('Erreur count Rentals', ['msg' => $e->getMessage()]);
            return 0;
        }
    }

    public function getRentalById(int $id): ?array
    {
        $url     = $this->baseUrl . '/rentals/' . $id;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Rental by ID KO', ['id' => $id, 'status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur rental by ID', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createRental(array $data): ?array
    {
        $url     = $this->baseUrl . '/rentals';
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            Log::info('Création rental', ['data' => $data]);
            $response = Http::withHeaders($headers)->timeout(15)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Création rental KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création rental', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function updateRental(int $id, array $data): ?array
    {
        $url     = $this->baseUrl . '/rentals/' . $id;
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Update rental KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur update rental', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteRental(int $id): bool
    {
        $url     = $this->baseUrl . '/rentals/' . $id;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->delete($url);

            if ($response->successful() || $response->status() === 204) {
                return true;
            }

            Log::warning('Suppression rental KO', ['status' => $response->status()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression rental', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function getRentalHistory(int $customerId): ?array
    {
        $url     = $this->baseUrl . '/rentals/history/' . $customerId;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Rental history KO', ['customerId' => $customerId, 'status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur rental history', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    private function getUserToken(): ?string
    {
        $userData = session('toad_user');
        if (!empty($userData['token'])) {
            return $userData['token'];
        }
        return config('services.toad.token') ?: null;
    }
}
