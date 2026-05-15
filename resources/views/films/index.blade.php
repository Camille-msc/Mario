
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Film Catalogue Management</h5>
                    <a href="{{ route('films.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Add a Film
                    </a>
                </div>

                <div class="card-body">
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
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Year</th>
                                        <th>Duration</th>
                                        <th>Rating</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($films as $film)
                                        <tr>
                                            <td>{{ $film['filmId'] ?? $film['id'] ?? 'N/A' }}</td>
                                            <td><strong>{{ $film['title'] ?? 'Untitled' }}</strong></td>
                                            <td>{{ Str::limit($film['description'] ?? 'No description', 80) }}</td>
                                            <td>{{ $film['releaseYear'] ?? 'N/A' }}</td>
                                            <td>{{ $film['length'] ?? 'N/A' }} min</td>
                                            <td>
                                                @if(isset($film['rating']))
                                                    <span class="badge bg-info">{{ $film['rating'] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}"
                                                       class="btn btn-sm btn-info">
                                                        View
                                                    </a>
                                                    <a href="{{ route('films.edit', $film['filmId'] ?? $film['id']) }}"
                                                       class="btn btn-sm btn-warning">
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('films.destroy', $film['filmId'] ?? $film['id']) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this film?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            Delete
                                                        </button>
                                                    </form>
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
                                Total: <strong>{{ $films->total() }}</strong> film(s)
                            </p>
                        </div>
                        {{ $films->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection