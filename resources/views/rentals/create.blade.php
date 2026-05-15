@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Rental</h5>
                    <a href="{{ route('rentals.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('rentals.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customerId" class="form-label">Customer ID <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('customerId') is-invalid @enderror"
                                       id="customerId"
                                       name="customerId"
                                       value="{{ old('customerId') }}"
                                       required
                                       min="1"
                                       placeholder="1">
                                @error('customerId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="inventoryId" class="form-label">Inventory ID <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('inventoryId') is-invalid @enderror"
                                       id="inventoryId"
                                       name="inventoryId"
                                       value="{{ old('inventoryId') }}"
                                       required
                                       min="1"
                                       placeholder="1">
                                @error('inventoryId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="staffId" class="form-label">Staff ID</label>
                                <input type="number"
                                       class="form-control @error('staffId') is-invalid @enderror"
                                       id="staffId"
                                       name="staffId"
                                       value="{{ old('staffId') }}"
                                       min="1"
                                       placeholder="Leave blank if none">
                                @error('staffId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="statusId" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('statusId') is-invalid @enderror"
                                        id="statusId"
                                        name="statusId"
                                        required>
                                    @foreach($statuses as $sid => $label)
                                        <option value="{{ $sid }}" {{ old('statusId', 3) == $sid ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('statusId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rentalDate" class="form-label">Rental Date <span class="text-danger">*</span></label>
                                <input type="datetime-local"
                                       class="form-control @error('rentalDate') is-invalid @enderror"
                                       id="rentalDate"
                                       name="rentalDate"
                                       value="{{ old('rentalDate', now()->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('rentalDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="returnDate" class="form-label">Return Date</label>
                                <input type="datetime-local"
                                       class="form-control @error('returnDate') is-invalid @enderror"
                                       id="returnDate"
                                       name="returnDate"
                                       value="{{ old('returnDate') }}">
                                <small class="form-text text-muted">Leave blank if not yet returned.</small>
                                @error('returnDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('rentals.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Rental
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
