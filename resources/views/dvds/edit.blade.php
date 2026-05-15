@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil"></i> Edit DVD
                    </h5>
                    <a href="{{ route('dvds.show', $film['filmId'] ?? $film['id']) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- DVD Information -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle"></i> DVD Information
                        </h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>DVD ID:</strong> #{{ $inventory['inventoryId'] ?? $inventory['id'] ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Film:</strong> {{ $film['title'] ?? 'Untitled' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Year:</strong> {{ $film['releaseYear'] ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Current Store:</strong> Store #{{ $inventory['storeId'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('dvds.update', $inventory['inventoryId'] ?? $inventory['id']) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="storeId" class="form-label">
                                <strong>New Store</strong> <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('storeId') is-invalid @enderror"
                                    id="storeId"
                                    name="storeId"
                                    required>
                                <option value="">-- Select a store --</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store['storeId'] ?? $store['id'] }}"
                                            {{ (old('storeId', $inventory['storeId']) == ($store['storeId'] ?? $store['id'])) ? 'selected' : '' }}>
                                        Store #{{ $store['storeId'] ?? $store['id'] }} - {{ $store['address'] ?? 'Address not available' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('storeId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                Select the new store to transfer this DVD to
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Warning:</strong> Changing the store will physically transfer the DVD to another location.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dvds.show', $film['filmId'] ?? $film['id']) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection