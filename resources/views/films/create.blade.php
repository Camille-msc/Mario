@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add a New Film</h5>
                    <a href="{{ route('films.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('films.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title') }}"
                                       required
                                       placeholder="Enter the film title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="releaseYear" class="form-label">Release Year <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('releaseYear') is-invalid @enderror"
                                       id="releaseYear"
                                       name="releaseYear"
                                       value="{{ old('releaseYear') }}"
                                       min="1900"
                                       max="2100"
                                       required
                                       placeholder="2024">
                                @error('releaseYear')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="Describe the film...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="languageId" class="form-label">Language <span class="text-danger">*</span></label>
                                <select class="form-select @error('languageId') is-invalid @enderror"
                                        id="languageId"
                                        name="languageId"
                                        required>
                                    <option value="">-- Select a language --</option>
                                    @foreach($languages as $id => $name)
                                        <option value="{{ $id }}" {{ old('languageId', 1) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('languageId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="length" class="form-label">Duration (minutes)</label>
                                <input type="number"
                                       class="form-control @error('length') is-invalid @enderror"
                                       id="length"
                                       name="length"
                                       value="{{ old('length') }}"
                                       min="1"
                                       max="500"
                                       placeholder="120">
                                @error('length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select @error('rating') is-invalid @enderror"
                                        id="rating"
                                        name="rating">
                                    <option value="">-- Select --</option>
                                    <option value="G" {{ old('rating') == 'G' ? 'selected' : '' }}>G</option>
                                    <option value="PG" {{ old('rating') == 'PG' ? 'selected' : '' }}>PG</option>
                                    <option value="PG-13" {{ old('rating') == 'PG-13' ? 'selected' : '' }}>PG-13</option>
                                    <option value="R" {{ old('rating') == 'R' ? 'selected' : '' }}>R</option>
                                    <option value="NC-17" {{ old('rating') == 'NC-17' ? 'selected' : '' }}>NC-17</option>
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="rentalDuration" class="form-label">Rental Duration (days) <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('rentalDuration') is-invalid @enderror"
                                       id="rentalDuration"
                                       name="rentalDuration"
                                       value="{{ old('rentalDuration', 3) }}"
                                       min="1"
                                       max="30"
                                       required
                                       placeholder="3">
                                <small class="form-text text-muted">Number of rental days (default: 3)</small>
                                @error('rentalDuration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="rentalRate" class="form-label">Rental Rate ($) <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('rentalRate') is-invalid @enderror"
                                       id="rentalRate"
                                       name="rentalRate"
                                       value="{{ old('rentalRate', 4.99) }}"
                                       min="0"
                                       step="0.01"
                                       required
                                       placeholder="4.99">
                                @error('rentalRate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="replacementCost" class="form-label">Replacement Cost ($) <span class="text-danger">*</span></label>
                                <input type="number"
                                       class="form-control @error('replacementCost') is-invalid @enderror"
                                       id="replacementCost"
                                       name="replacementCost"
                                       value="{{ old('replacementCost', 19.99) }}"
                                       min="0"
                                       step="0.01"
                                       required
                                       placeholder="19.99">
                                @error('replacementCost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="specialFeatures" class="form-label">Special Features</label>
                                <select class="form-select @error('specialFeatures') is-invalid @enderror"
                                        id="specialFeatures"
                                        name="specialFeatures[]"
                                        multiple
                                        size="4">
                                    @foreach($specialFeatures as $value => $label)
                                        <option value="{{ $value }}" {{ in_array($value, old('specialFeatures', [])) ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple options</small>
                                @error('specialFeatures')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('films.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Film
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection