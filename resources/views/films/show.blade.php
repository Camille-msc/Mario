@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Film Details</h5>
                    <a href="{{ route('films.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3>{{ $film['title'] ?? 'Untitled' }}</h3>
                            <p class="text-muted">{{ $film['description'] ?? 'No description available.' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if(isset($film['rating']))
                                <span class="badge bg-info fs-5">{{ $film['rating'] }}</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Release Year</dt>
                        <dd class="col-sm-9">{{ $film['releaseYear'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Language</dt>
                        <dd class="col-sm-9">
                            @php
                                $langId = $film['languageId'] ?? $film['originalLanguageId'] ?? null;
                            @endphp
                            {{ $langId && isset($languages[$langId]) ? $languages[$langId] : 'N/A' }}
                        </dd>

                        <dt class="col-sm-3">Duration</dt>
                        <dd class="col-sm-9">{{ $film['length'] ?? 'N/A' }} minutes</dd>

                        <input type="hidden" name="replacementCost" value="{{ $film['replacementCost'] }}">

                        <dt class="col-sm-3">Rating</dt>
                        <dd class="col-sm-9">{{ $film['rating'] ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Special Features</dt>
                        <dd class="col-sm-9">
                            @if(isset($film['specialFeatures']) && $film['specialFeatures'])
                                @php
                                    $features = explode(',', $film['specialFeatures']);
                                    $translatedFeatures = [];
                                    foreach ($features as $feature) {
                                        $feature = trim($feature);
                                        $translatedFeatures[] = $specialFeatures[$feature] ?? $feature;
                                    }
                                @endphp
                                {{ implode(', ', $translatedFeatures) }}
                            @else
                                None
                            @endif
                        </dd>

                        <dt class="col-sm-3">Last Updated</dt>
                        <dd class="col-sm-9">{{ $film['lastUpdate'] ?? 'N/A' }}</dd>
                    </dl>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('films.edit', $film['filmId'] ?? $film['id']) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('films.destroy', $film['filmId'] ?? $film['id']) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this film?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection