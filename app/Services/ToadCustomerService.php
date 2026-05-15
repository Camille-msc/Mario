<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadCustomerService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function getAllCustomers(): ?array
    {
        $url     = $this->baseUrl . '/customers';
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            Log::info('Appel API Customers', ['url' => $url]);

            // timeout long car le payload brut fait ~28 Mo (chaque customer embarque toutes ses locations)
            $response = Http::withHeaders($headers)->timeout(60)->get($url);

            if (!$response->successful()) {
                Log::warning('Customers API KO', ['status' => $response->status()]);
                return null;
            }

            // on retire les tableaux "rentals" du JSON brut avant le décodage PHP pour éviter de dépasser 128 Mo en mémoire
            $body = $this->stripRentalsJson($response->body());
            $data = json_decode($body, true);

            if (!is_array($data)) {
                Log::error('Customers API: json_decode failed', ['bodyLen' => strlen($body)]);
                return null;
            }

            Log::info('Customers API OK', ['count' => count($data)]);
            return $data;

        } catch (\Throwable $e) {
            Log::error('Erreur API Customers', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    // remplace chaque bloc "rentals":[...] par "rentals":[] via comptage de crochets -
    // plus fiable qu'une regex sur du JSON arbitrairement imbriqué
    private function stripRentalsJson(string $body): string
    {
        $result    = '';
        $offset    = 0;
        $marker    = '"rentals":';
        $markerLen = strlen($marker);
        $bodyLen   = strlen($body);

        while (($pos = strpos($body, $marker, $offset)) !== false) {
            $result .= substr($body, $offset, $pos - $offset) . '"rentals":[]';

            $i = $pos + $markerLen;
            while ($i < $bodyLen && $body[$i] !== '[') {
                $i++;
            }

            if ($i >= $bodyLen) {
                $offset = $pos + $markerLen;
                continue;
            }

            $depth = 0;
            while ($i < $bodyLen) {
                $c = $body[$i];
                if ($c === '[' || $c === '{') {
                    $depth++;
                } elseif ($c === ']' || $c === '}') {
                    $depth--;
                    if ($depth === 0) { $i++; break; }
                }
                $i++;
            }

            $offset = $i;
        }

        return $result . substr($body, $offset);
    }

    public function getCustomerById(int $id): ?array
    {
        $url     = $this->baseUrl . '/customers/' . $id;
        $headers = ['Accept' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($url);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function createCustomer(array $data): ?array
    {
        $url     = $this->baseUrl . '/customers';
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        if (!isset($data['createDate'])) {
            $data['createDate'] = now()->format('Y-m-d\TH:i:s');
        }
        $data['lastUpdate'] = now()->format('Y-m-d\TH:i:s');

        try {
            $response = Http::withHeaders($headers)->timeout(15)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Création customer KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur création customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function updateCustomer(int $id, array $data): ?array
    {
        $url     = $this->baseUrl . '/customers/' . $id;
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $token   = $this->getUserToken();

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        $data['lastUpdate'] = now()->format('Y-m-d\TH:i:s');

        try {
            $response = Http::withHeaders($headers)->timeout(15)->put($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Mise à jour customer KO', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur mise à jour customer', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteCustomer(int $id): bool
    {
        $url     = $this->baseUrl . '/customers/' . $id;
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

            Log::warning('Suppression customer KO', ['status' => $response->status()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Erreur suppression customer', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    private function getUserToken(): ?string
    {
        $staticToken = config('services.toad.token');
        if (!empty($staticToken)) {
            return $staticToken;
        }
        return session('toad_user')['token'] ?? null;
    }
}
