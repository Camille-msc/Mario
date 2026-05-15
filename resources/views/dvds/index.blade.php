@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">DVD Stock Management</h5>
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

                    @if (empty($films))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No films available or an error occurred while retrieving data from the API.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Year</th>
                                        <th>Duration</th>
                                        <th>Number of DVDs</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($films as $film)
                                        <tr style="cursor: pointer;" onclick="window.location='{{ route('dvds.show', $film['filmId'] ?? $film['id']) }}'">
                                            <td><strong>{{ $film['title'] ?? 'Untitled' }}</strong></td>
                                            <td>{{ Str::limit($film['description'] ?? 'No description', 80) }}</td>
                                            <td>{{ $film['releaseYear'] ?? 'N/A' }}</td>
                                            <td>{{ $film['length'] ?? 'N/A' }} min</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $film['dvdCount'] ?? 0 }} DVD(s)
                                                </span>
                                            </td>
                                            <td onclick="event.stopPropagation();">
                                                <button type="button"
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#addDvdModal"
                                                        data-film-id="{{ $film['filmId'] ?? $film['id'] }}"
                                                        data-film-title="{{ $film['title'] ?? 'Untitled' }}">
                                                    <i class="bi bi-plus-circle"></i> Add DVD
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Total: <strong>{{ count($films) }}</strong> film(s)
                            </p>
                            <p class="text-muted">
                                <i class="bi bi-hand-index"></i>
                                Click on a film to view its DVDs
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
                    <input type="hidden" name="filmId" id="modalFilmId">

                    <div class="mb-3">
                        <label class="form-label"><strong>Film:</strong></label>
                        <p id="modalFilmTitle" class="text-muted"></p>
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

@endsection

@section('scripts')
<script>
    // Populate the modal with the selected film's data
    document.addEventListener('DOMContentLoaded', function() {
        const addDvdModal = document.getElementById('addDvdModal');

        addDvdModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const filmId = button.getAttribute('data-film-id');
            const filmTitle = button.getAttribute('data-film-title');

            document.getElementById('modalFilmId').value = filmId;
            document.getElementById('modalFilmTitle').textContent = filmTitle;
        });
    });
</script>
@endsection