<?php

namespace App\Http\Controllers;

use App\Services\ToadInventoryService;
use App\Services\ToadFilmService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private ToadInventoryService $inventoryService;
    private ToadFilmService $filmService;

    public function __construct(ToadInventoryService $inventoryService, ToadFilmService $filmService)
    {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
        $this->filmService      = $filmService;
    }

    public function index()
    {
        $films  = $this->filmService->getAllFilms();
        $stores = $this->inventoryService->getAllStores();

        if ($films) {
            foreach ($films as &$film) {
                $inventories       = $this->inventoryService->getInventoriesByFilmId($film['filmId'] ?? $film['id']);
                $film['dvdCount']  = $inventories ? count($inventories) : 0;
            }
        }

        return view('dvds.index', [
            'films'  => $films ?? [],
            'stores' => $stores ?? [],
        ]);
    }

    public function show($filmId)
    {
        $film = $this->filmService->getFilmById($filmId);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        $inventories = $this->inventoryService->getInventoriesByFilmId($filmId);

        if ($inventories) {
            foreach ($inventories as &$inventory) {
                $availability          = $this->inventoryService->checkInventoryAvailability($inventory['inventoryId']);
                $inventory['available'] = $availability['available'] ?? true;
            }
        }

        return view('dvds.show', [
            'film'        => $film,
            'inventories' => $inventories ?? [],
            'stores'      => $this->inventoryService->getAllStores() ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'filmId'  => 'required|integer|min:1',
            'storeId' => 'required|integer|min:1',
        ], [
            'filmId.required'  => 'L\'ID du film est obligatoire.',
            'storeId.required' => 'Le store est obligatoire.',
        ]);

        $newInventory = $this->inventoryService->createInventory($validatedData);

        if ($newInventory) {
            return redirect()->route('dvds.show', $validatedData['filmId'])->with('success', 'DVD ajouté avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'ajout du DVD. Veuillez réessayer.');
    }

    public function edit($inventoryId)
    {
        $inventory = $this->inventoryService->getInventoryById($inventoryId);

        if (!$inventory) {
            abort(404, 'DVD non trouvé');
        }

        return view('dvds.edit', [
            'inventory' => $inventory,
            'film'      => $this->filmService->getFilmById($inventory['filmId']),
            'stores'    => $this->inventoryService->getAllStores() ?? [],
        ]);
    }

    public function update(Request $request, $inventoryId)
    {
        $validatedData = $request->validate([
            'storeId' => 'required|integer|min:1',
        ], [
            'storeId.required' => 'Le store est obligatoire.',
        ]);

        $inventory = $this->inventoryService->getInventoryById($inventoryId);

        if (!$inventory) {
            abort(404, 'DVD non trouvé');
        }

        $payload = [
            'filmId'  => (int) $inventory['filmId'],
            'storeId' => (int) $validatedData['storeId'],
        ];

        $updatedInventory = $this->inventoryService->updateInventory($inventoryId, $payload);

        if ($updatedInventory) {
            return redirect()->route('dvds.show', $inventory['filmId'])->with('success', 'DVD mis à jour avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du DVD. Veuillez réessayer.');
    }

    public function destroy($inventoryId)
    {
        $inventory = $this->inventoryService->getInventoryById($inventoryId);

        if (!$inventory) {
            abort(404, 'DVD non trouvé');
        }

        // on vérifie d'abord que le DVD n'est pas en cours de location avant toute suppression
        $availability = $this->inventoryService->checkInventoryAvailability($inventoryId);

        if (isset($availability['available']) && !$availability['available']) {
            return redirect()->back()->with('error', 'Impossible de supprimer ce DVD. Il est actuellement en location.');
        }

        // Peach a une contrainte FK RESTRICT sur rental.inventory_id - on supprime l'historique avant le DVD
        $rentals = $this->inventoryService->getRentalsByInventoryId($inventoryId);

        if ($rentals && count($rentals) > 0) {
            foreach ($rentals as $rental) {
                $rentalId = $rental['rentalId'] ?? $rental['id'];
                if (!$this->inventoryService->deleteRental($rentalId)) {
                    return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'historique de location. Veuillez réessayer.');
                }
            }
        }

        $success = $this->inventoryService->deleteInventory($inventoryId);

        if ($success) {
            return redirect()->route('dvds.show', $inventory['filmId'])->with('success', 'DVD supprimé avec succès !');
        }

        return redirect()->back()->with('error', 'Erreur lors de la suppression du DVD. Veuillez réessayer.');
    }
}
