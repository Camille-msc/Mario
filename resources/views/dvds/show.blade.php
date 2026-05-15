@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-disc"></i>
                        DVDs for: <strong>{{ $film['title'] ?? 'Unknown Film' }}</strong>
                    </h5>
                    <div>
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#addDvdModal">
                            <i class="bi bi-plus-circle"></i> Add DVD
                        </button>
                        <a href="{{ route('dvds.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Film information -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Year:</strong> {{ $film['releaseYear'] ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $film['length'] ?? 'N/A' }} min</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Rating:</strong>
                                    @if(isset($film['rating']))
                                        <span class="badge bg-info">{{ $film['rating'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </p>
                                <p class="mb-0"><strong>Total DVDs:</strong> {{ count($inventories) }}</p>
                            </div>
                        </div>
                    </div>

                    @if (empty($inventories))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No DVDs available for this film.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>DVD ID</th>
                                        <th>Film Title</th>
                                        <th>Store ID</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inventories as $inventory)
                                        <tr>
                                            <td><strong>#{{ $inventory['inventoryId'] ?? $inventory['id'] ?? 'N/A' }}</strong></td>
                                            <td>{{ $film['title'] ?? 'Untitled' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    Store #{{ $inventory['storeId'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($inventory['available']) && $inventory['available'])
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Available
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle"></i> On Rental
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dvds.edit', $inventory['inventoryId'] ?? $inventory['id']) }}"
                                                       class="btn btn-sm btn-warning">
                                                        Edit
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteDvdModal"
                                                            data-inventory-id="{{ $inventory['inventoryId'] ?? $inventory['id'] }}"
                                                            data-inventory-available="{{ $inventory['available'] ? 'true' : 'false' }}">
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Total: <strong>{{ count($inventories) }}</strong> DVD(s) for this film
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add a DVD -->
<div class="modal fade" id="addDvdModal" tabindex="-1" aria-labelledby="addDvdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dvds.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDvdModalLabel">Add a DVD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="filmId" value="{{ $film['filmId'] ?? $film['id'] }}">

                    <div class="mb-3">
                        <label class="form-label"><strong>Film:</strong></label>
                        <p class="text-muted">{{ $film['title'] ?? 'Untitled' }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="storeId" class="form-label">Store <span class="text-danger">*</span></label>
                        <select class="form-select @error('storeId') is-invalid @enderror"
                                id="storeId"
                                name="storeId"
                                required>
                            <option value="">-- Select a store --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store['storeId'] ?? $store['id'] }}">
                                    Store #{{ $store['storeId'] ?? $store['id'] }} - {{ $store['address'] ?? 'Address not available' }}
                                </option>
                            @endforeach
                        </select>
                        @error('storeId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> Select the store where this DVD will be available
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Delete a DVD -->
<div class="modal fade" id="deleteDvdModal" tabindex="-1" aria-labelledby="deleteDvdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="deleteDvdForm" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteDvdModalLabel">
                        <i class="bi bi-exclamation-triangle"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Are you sure you want to delete this DVD?</p>
                    <p id="deleteWarning" class="text-danger small" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Warning:</strong> This DVD has a rental history. Deleting it will also remove the associated history.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Handle DVD deletion modal
    document.addEventListener('DOMContentLoaded', function() {
        const deleteDvdModal = document.getElementById('deleteDvdModal');

        deleteDvdModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const inventoryId = button.getAttribute('data-inventory-id');
            const available = button.getAttribute('data-inventory-available');

            const form = document.getElementById('deleteDvdForm');
            form.action = "{{ route('dvds.destroy', ':id') }}".replace(':id', inventoryId);

            // Show a warning if the DVD has been rented (simplified logic)
            // In a real-world case this information should come from the backend
            const deleteWarning = document.getElementById('deleteWarning');
            if (available === 'false') {
                deleteWarning.style.display = 'block';
            } else {
                deleteWarning.style.display = 'none';
            }
        });
    });
</script>
@endsection