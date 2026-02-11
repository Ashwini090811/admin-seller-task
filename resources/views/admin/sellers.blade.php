@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Sellers</h4>
    <form method="POST" action="{{ request()->getBaseUrl() }}/admin/logout">
        @csrf
        <button class="btn btn-sm btn-outline-danger" type="submit">Logout</button>
    </form>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">Add Seller</h6>
                <form method="POST" action="{{ request()->getBaseUrl() }}/admin/sellers">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" required>
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}" required>
                            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}" required>
                        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Skills (comma separated)</label>
                        <input type="text" name="skills" class="form-control @error('skills') is-invalid @enderror" value="{{ old('skills') }}" required>
                        @error('skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Create Seller</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3">Seller List</h6>
                @if ($error)
                    <div class="alert alert-warning">{{ $error }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Country</th>
                                <th>State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sellers as $seller)
                                <tr>
                                    <td>{{ $seller['name'] ?? '-' }}</td>
                                    <td>{{ $seller['email'] ?? '-' }}</td>
                                    <td>{{ $seller['mobile'] ?? '-' }}</td>
                                    <td>{{ $seller['country'] ?? '-' }}</td>
                                    <td>{{ $seller['state'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No sellers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
