<?php

namespace App\Http\Controllers;

use App\Services\ToadCustomerService;
use App\Services\ToadRentalService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private ToadCustomerService $customerService;
    private ToadRentalService $rentalService;

    public function __construct(ToadCustomerService $customerService, ToadRentalService $rentalService)
    {
        $this->middleware('auth');
        $this->customerService = $customerService;
        $this->rentalService   = $rentalService;
    }

    public function index()
    {
        return view('customers.index', ['allowedLimits' => [10, 20, 50]]);
    }

    // endpoint JSON pour la pagination côté client - appelé en AJAX par la vue via fetch()
    public function getData(Request $request)
    {
        $validated = $request->validate([
            'page'  => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ]);

        $page  = (int) ($validated['page'] ?? 1);
        $limit = (int) ($validated['limit'] ?? 10);

        $all        = $this->customerService->getAllCustomers() ?? [];
        $total      = count($all);
        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 1;
        $customers  = array_slice($all, ($page - 1) * $limit, $limit);

        return response()->json([
            'customers'      => $customers,
            'totalCustomers' => $total,
            'totalPages'     => max(1, $totalPages),
            'currentPage'    => $page,
        ]);
    }

    public function show($id)
    {
        $customer = $this->customerService->getCustomerById((int) $id);

        if (!$customer) {
            abort(404, 'Client non trouvé');
        }

        $rentalHistory = $this->rentalService->getRentalHistory((int) $id) ?? [];

        return view('customers.show', [
            'customer'      => $customer,
            'rentalHistory' => $rentalHistory,
        ]);
    }

    public function edit($id)
    {
        $customer = $this->customerService->getCustomerById((int) $id);

        if (!$customer) {
            abort(404, 'Client non trouvé');
        }

        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'firstName'  => 'required|string|max:45',
            'lastName'   => 'required|string|max:45',
            'email'      => 'required|email|max:50',
            'storeId'    => 'required|integer|min:1',
            'addressId'  => 'required|integer|min:1',
        ], [
            'firstName.required'  => 'Le prénom est obligatoire.',
            'lastName.required'   => 'Le nom est obligatoire.',
            'email.required'      => 'L\'adresse email est obligatoire.',
            'email.email'         => 'L\'adresse email n\'est pas valide.',
            'storeId.required'    => 'L\'identifiant du magasin est obligatoire.',
            'addressId.required'  => 'L\'identifiant de l\'adresse est obligatoire.',
        ]);

        $validatedData['active'] = $request->has('active');

        // on préserve la createDate d'origine - Toad la requiert dans le PUT mais ne doit pas être modifiée
        $original = $this->customerService->getCustomerById((int) $id);
        if ($original && isset($original['createDate'])) {
            $validatedData['createDate'] = $original['createDate'];
        }

        $updatedCustomer = $this->customerService->updateCustomer((int) $id, $validatedData);

        if ($updatedCustomer) {
            return redirect()
                ->route('customers.show', $updatedCustomer['customerId'])
                ->with('success', 'Client mis à jour avec succès !');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour du client. Veuillez réessayer.');
    }

    public function destroy($id)
    {
        // Peach a une contrainte FK RESTRICT sur rental.customer_id - on supprime les locations d'abord
        // sinon Toad retourne 403 (Spring Security bloque la page /error générée par la violation FK)
        $rentals = $this->rentalService->getRentalHistory((int) $id) ?? [];
        foreach ($rentals as $rental) {
            if (!empty($rental['rentalId'])) {
                $this->rentalService->deleteRental((int) $rental['rentalId']);
            }
        }

        $success = $this->customerService->deleteCustomer((int) $id);

        if ($success) {
            return redirect()->route('customers.index')->with('success', 'Client supprimé avec succès !');
        }

        return redirect()->back()->with('error', 'Impossible de supprimer ce client. Il possède peut-être des locations avec statut "panier" qui n\'ont pas pu être supprimées automatiquement.');
    }
}
