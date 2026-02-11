@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Products</h4>
    <form method="POST" action="{{ request()->getBaseUrl() }}/seller/logout">
        @csrf
        <button class="btn btn-sm btn-outline-danger" type="submit">Logout</button>
    </form>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">Add Product</h6>
                <form method="POST" action="{{ request()->getBaseUrl() }}/seller/products" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-2">Brand 1</h6>
                        <div class="mb-2">
                            <label class="form-label">Name</label>
                            <input type="text" name="brand1_name" class="form-control @error('brand1_name') is-invalid @enderror" value="{{ old('brand1_name') }}" required>
                            @error('brand1_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Detail</label>
                            <input type="text" name="brand1_detail" class="form-control @error('brand1_detail') is-invalid @enderror" value="{{ old('brand1_detail') }}">
                            @error('brand1_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Image</label>
                            <input type="file" name="brand1_image" class="form-control @error('brand1_image') is-invalid @enderror" accept="image/*">
                            @error('brand1_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" name="brand1_price" class="form-control @error('brand1_price') is-invalid @enderror" value="{{ old('brand1_price') }}" required>
                            @error('brand1_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="mb-2">Brand 2 (optional)</h6>
                        <div class="mb-2">
                            <label class="form-label">Name</label>
                            <input type="text" name="brand2_name" class="form-control @error('brand2_name') is-invalid @enderror" value="{{ old('brand2_name') }}">
                            @error('brand2_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Detail</label>
                            <input type="text" name="brand2_detail" class="form-control @error('brand2_detail') is-invalid @enderror" value="{{ old('brand2_detail') }}">
                            @error('brand2_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Image</label>
                            <input type="file" name="brand2_image" class="form-control @error('brand2_image') is-invalid @enderror" accept="image/*">
                            @error('brand2_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" name="brand2_price" class="form-control @error('brand2_price') is-invalid @enderror" value="{{ old('brand2_price') }}">
                            @error('brand2_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Create Product</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">Product List</h6>
                    <form method="GET" action="{{ request()->getBaseUrl() }}/seller/products" class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 small">Per page</label>
                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach ([5, 10, 20, 50] as $size)
                                <option value="{{ $size }}" @selected(($pagination['per_page'] ?? 10) == $size)>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @if ($error)
                    <div class="alert alert-warning">{{ $error }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Brands</th>
                                <th>Total Price</th>
                                <th>PDF</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $brands = $product['brands'] ?? [];
                                    $total = collect($brands)->sum('price');
                                @endphp
                                <tr>
                                    <td>{{ $product['name'] ?? '-' }}</td>
                                    <td>{{ $product['description'] ?? '-' }}</td>
                                    <td>{{ count($brands) }}</td>
                                    <td>{{ number_format($total, 2) }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ request()->getBaseUrl() }}/seller/products/{{ $product['id'] }}/pdf" target="_blank">
                                            View PDF
                                        </a>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ request()->getBaseUrl() }}/seller/products/{{ $product['id'] }}" onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (!empty($pagination) && $pagination['last_page'] > 1)
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                        <div class="small text-muted">
                            Showing page {{ $pagination['current_page'] }} of {{ $pagination['last_page'] }} ({{ $pagination['total'] }} total)
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item {{ $pagination['prev_url'] ? '' : 'disabled' }}">
                                    <a class="page-link" href="{{ $pagination['prev_url'] ?? '#' }}">Previous</a>
                                </li>
                                @foreach ($pagination['page_urls'] as $link)
                                    <li class="page-item {{ $link['is_current'] ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $link['url'] }}">{{ $link['page'] }}</a>
                                    </li>
                                @endforeach
                                <li class="page-item {{ $pagination['next_url'] ? '' : 'disabled' }}">
                                    <a class="page-link" href="{{ $pagination['next_url'] ?? '#' }}">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
