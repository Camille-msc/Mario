<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadFilmService
{
    private string $baseUrl;

    public function __construct()
    {
        // rtrim pour éviter le double slash si TOAD_API_URL contient un slash final
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllFilms(int $limit = 20, int $offset = 0): ?array
    {
        $url     = $this->baseUrl . '/films';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            Log::info('Appel API Films', ['url' => $url]);

            $response = Http::withHeaders($headers)->timeout(10)->get($url, ['limit' => $limit, 'offset' => $offset]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Films API KO', ['status' => $response->status()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getCountFilms(): int
    {
        $url     = $this->baseUrl . '/films/count';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? (int) $response->body() : 0;
        } catch (\Throwable $e) {
            Log::error('Erreur count Films', ['msg' => $e->getMessage()]);
            return 0;
        }
    }

    public function getFilmById(int $id): ?array
    {
        $url     = $this->baseUrl . '/films/' . $id;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createFilm(array $data): ?array
    {
        $url     = $this->baseUrl . '/films';
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            Log::info('Création film', ['data' => $data]);
            $response = Http::withHeaders($headers)->timeout(10)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Création film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function updateFilm(int $id, array $data): ?array
    {
        $url     = $this->baseUrl . '/films/' . $id;
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

            Log::warning('Mise à jour film KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteFilm(int $id): bool
    {
        $url     = $this->baseUrl . '/films/' . $id;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(10)->delete($url);

            // 204 No Content est une suppression réussie sans corps de réponse
            if ($response->successful() || $response->status() === 204) {
                return true;
            }

            Log::warning('Suppression film KO', ['status' => $response->status()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression film', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    private function getUserToken(): ?string
    {
        return session('toad_user')['token'] ?? null;
    }
}
