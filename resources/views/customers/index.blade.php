@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Customer Management</h5>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">Total: <strong id="totalCustomers">-</strong> customer(s)</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="limit" class="mb-0">Show:</label>
                            <select id="limit" class="form-select form-select-sm" style="width: auto;">
                                @foreach ($allowedLimits as $l)
                                    <option value="{{ $l }}">{{ $l }}</option>
                                @endforeach
                            </select>
                            <span class="text-muted">per page</span>
                        </div>
                    </div>

                    <div id="loading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading customers from API, please wait...</p>
                    </div>

                    <div id="noCustomers" class="alert alert-warning" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        No customers available or an error occurred while retrieving data from the API.
                    </div>

                    <div id="customersContainer" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Store</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="customersTableBody">
                                </tbody>
                            </table>
                        </div>

                        <div id="paginationContainer" class="mt-3 d-flex justify-content-between align-items-center">
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle"></i>
                                Page <strong id="currentPageInfo">1</strong> of <strong id="totalPagesInfo">1</strong>
                            </p>
                            <nav aria-label="Customer pagination">
                                <ul class="pagination mb-0" id="paginationList">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const CustomersPagination = {
        currentPage: 1,
        limit: 10,
        totalPages: 1,
        csrfToken: '{{ csrf_token() }}',
        dataUrl: '{{ route("customers.data") }}',
        showUrl: '{{ url("customers") }}',
        editUrl: '{{ url("customers") }}',
        deleteUrl: '{{ url("customers") }}',

        init() {
            this.limit = 10;
            document.getElementById('limit').value = this.limit;
            this.loadCustomers();
            document.getElementById('limit').addEventListener('change', (e) => {
                this.limit = parseInt(e.target.value);
                this.currentPage = 1;
                this.loadCustomers();
            });
        },

        async loadCustomers() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('customersContainer').style.display = 'none';
            document.getElementById('noCustomers').style.display = 'none';

            try {
                const response = await fetch(this.dataUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ page: this.currentPage, limit: this.limit })
                });

                const data = await response.json();
                document.getElementById('loading').style.display = 'none';

                if (!data.customers || data.customers.length === 0) {
                    document.getElementById('noCustomers').style.display = 'block';
                    return;
                }

                this.totalPages = data.totalPages;
                this.renderCustomers(data.customers);
                this.renderPagination(data);
                document.getElementById('customersContainer').style.display = 'block';

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('noCustomers').style.display = 'block';
            }
        },

        renderCustomers(customers) {
            const tbody = document.getElementById('customersTableBody');
            tbody.innerHTML = '';

            customers.forEach(c => {
                const id        = c.customerId ?? 'N/A';
                const firstName = c.firstName ?? '';
                const lastName  = c.lastName ?? '';
                const email     = c.email ?? 'N/A';
                const storeId   = c.storeId ?? 'N/A';
                const active    = c.active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${id}</td>
                    <td>${this.escapeHtml(firstName)}</td>
                    <td><strong>${this.escapeHtml(lastName)}</strong></td>
                    <td>${this.escapeHtml(email)}</td>
                    <td>${storeId}</td>
                    <td>${active}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="${this.showUrl}/${id}" class="btn btn-info btn-sm" title="View details">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="${this.editUrl}/${id}/edit" class="btn btn-warning btn-sm text-white" title="Edit">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="${this.deleteUrl}/${id}" method="POST" style="display: inline;"
                                  onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                <input type="hidden" name="_token" value="${this.csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        },

        renderPagination(data) {
            document.getElementById('totalCustomers').textContent = data.totalCustomers;
            document.getElementById('currentPageInfo').textContent = data.currentPage;
            document.getElementById('totalPagesInfo').textContent = data.totalPages;

            if (data.totalPages <= 1) {
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }
            document.getElementById('paginationContainer').style.display = 'flex';

            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            paginationList.appendChild(this.createPageItem('«', 1, data.currentPage === 1));
            paginationList.appendChild(this.createPageItem('‹', Math.max(1, data.currentPage - 1), data.currentPage === 1));

            const start = Math.max(1, data.currentPage - 2);
            const end   = Math.min(data.totalPages, data.currentPage + 2);

            if (start > 1) {
                paginationList.appendChild(this.createPageItem('1', 1, false));
                if (start > 2) paginationList.appendChild(this.createPageItem('...', null, true));
            }

            for (let i = start; i <= end; i++) {
                paginationList.appendChild(this.createPageItem(i.toString(), i, false, i === data.currentPage));
            }

            if (end < data.totalPages) {
                if (end < data.totalPages - 1) paginationList.appendChild(this.createPageItem('...', null, true));
                paginationList.appendChild(this.createPageItem(data.totalPages.toString(), data.totalPages, false));
            }

            paginationList.appendChild(this.createPageItem('›', Math.min(data.totalPages, data.currentPage + 1), data.currentPage === data.totalPages));
            paginationList.appendChild(this.createPageItem('»', data.totalPages, data.currentPage === data.totalPages));
        },

        createPageItem(text, page, disabled, active = false) {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');

            if (disabled || page === null) {
                li.innerHTML = `<span class="page-link">${text}</span>`;
            } else {
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = text;
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.currentPage = page;
                    this.loadCustomers();
                });
                li.appendChild(a);
            }

            return li;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    document.addEventListener('DOMContentLoaded', () => CustomersPagination.init());
</script>
@endsection
