<?php

namespace App\Http\Controllers;

use App\Services\ToadRentalService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RentalController extends Controller
{
    private ToadRentalService $rentalService;

    private array $statuses = [
        1 => 'Terminé',
        2 => 'Dans le panier',
        3 => 'En cours',
    ];

    // le statut 2 "dans le panier" est exclu du dropdown - /rentals/all ne le retourne pas,
    // donc l'assigner ferait disparaître la location de la liste immédiatement
    private array $editableStatuses = [
        1 => 'Terminé',
        3 => 'En cours',
    ];

    public function __construct(ToadRentalService $rentalService)
    {
        $this->middleware('auth');
        $this->rentalService = $rentalService;
    }

    public function index(Request $request)
    {
        $perPage = 20;
        $page    = (int) $request->get('page', 1);
        $offset  = ($page - 1) * $perPage;

        $rentals = $this->rentalService->getAllRentals($perPage, $offset);
        $total   = $this->rentalService->getRentalsCount();

        $paginator = new LengthAwarePaginator(
            $rentals ?? [],
            $total,
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return view('rentals.index', [
            'rentals'          => $paginator,
            'statuses'         => $this->statuses,
            'editableStatuses' => $this->editableStatuses,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'statusId'    => 'required|integer|in:1,2,3',
            'rentalDate'  => 'required',
            'inventoryId' => 'required|integer',
            'customerId'  => 'required|integer',
        ]);

        // on recharge la location complète pour récupérer le staffId - il est absent du formulaire
        // mais obligatoire (NOT NULL en base) pour le PUT Toad
        $existing = $this->rentalService->getRentalById((int) $id);
        $staffId  = $existing['staffId'] ?? null;

        $data = [
            'rentalId'    => (int) $id,
            'rentalDate'  => $request->input('rentalDate'),
            'inventoryId' => (int) $request->input('inventoryId'),
            'customerId'  => (int) $request->input('customerId'),
            'staffId'     => $staffId,
            'returnDate'  => $request->input('returnDate') ?: null,
            'statusId'    => (int) $request->input('statusId'),
        ];

        $updated = $this->rentalService->updateRental((int) $id, $data);

        if ($updated) {
            return redirect()->route('rentals.index')->with('success', 'Statut mis à jour avec succès !');
        }

        return redirect()->route('rentals.index')->with('error', 'Erreur lors de la mise à jour du statut.');
    }

    public function destroy($id)
    {
        $success = $this->rentalService->deleteRental((int) $id);

        if ($success) {
            return redirect()->route('rentals.index')->with('success', 'Location supprimée avec succès !');
        }

        return redirect()->back()->with('error', 'Erreur lors de la suppression de la location. Veuillez réessayer.');
    }
}
