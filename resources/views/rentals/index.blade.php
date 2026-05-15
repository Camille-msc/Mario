@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Rental Management</h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle"></i>
                        Cette liste affiche uniquement les locations avec statut <strong>Terminé</strong> ou <strong>En cours</strong>.
                        Les locations <strong>Dans le panier</strong> (statut 2) sont exclues par l'API.
                    </div>

                    @if($rentals->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No rentals available or an error occurred while retrieving data from the API.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Film</th>
                                        <th>Rental Date</th>
                                        <th>Customer (ID)</th>
                                        <th>Inventory (ID)</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentals as $rental)
                                        <tr>
                                            <td>{{ $rental['rentalId'] ?? 'N/A' }}</td>
                                            <td><strong>{{ $rental['filmTitle'] ?? 'N/A' }}</strong></td>
                                            <td>{{ $rental['rentalDate'] ? \Carbon\Carbon::parse($rental['rentalDate'])->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>{{ $rental['customerId'] ?? 'N/A' }}</td>
                                            <td>{{ $rental['inventoryId'] ?? 'N/A' }}</td>
                                            <td>{{ $rental['returnDate'] ? \Carbon\Carbon::parse($rental['returnDate'])->format('d/m/Y H:i') : '—' }}</td>
                                            <td>
                                                @php $currentSid = $rental['statusId'] ?? null; @endphp
                                                @if($currentSid == 3)
                                                    <span class="badge bg-primary">{{ $statuses[3] }}</span>
                                                @elseif($currentSid == 2)
                                                    <span class="badge bg-warning text-dark">{{ $statuses[2] }}</span>
                                                @elseif($currentSid == 1)
                                                    <span class="badge bg-success">{{ $statuses[1] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 align-items-center flex-wrap">
                                                    {{-- Changement de statut --}}
                                                    <form action="{{ route('rentals.updateStatus', $rental['rentalId']) }}"
                                                          method="POST"
                                                          class="d-flex gap-1 align-items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="rentalDate"  value="{{ $rental['rentalDate'] ?? '' }}">
                                                        <input type="hidden" name="inventoryId" value="{{ $rental['inventoryId'] ?? '' }}">
                                                        <input type="hidden" name="customerId"  value="{{ $rental['customerId'] ?? '' }}">
                                                        <input type="hidden" name="returnDate"  value="{{ $rental['returnDate'] ?? '' }}">
                                                        <select name="statusId" class="form-select form-select-sm" style="width:auto">
                                                            @foreach($editableStatuses as $sid => $label)
                                                                <option value="{{ $sid }}"
                                                                    {{ ($rental['statusId'] ?? null) == $sid ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-primary">OK</button>
                                                    </form>

                                                    {{-- Suppression --}}
                                                    <form action="{{ route('rentals.destroy', $rental['rentalId']) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Supprimer cette location ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle"></i>
                                Total: <strong>{{ $rentals->total() }}</strong> rental(s)
                            </p>
                            {{ $rentals->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
