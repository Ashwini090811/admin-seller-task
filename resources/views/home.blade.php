@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Welcome</h5>
                <p class="card-text">Use the quick links below to log in as admin or seller.</p>
                <div class="d-flex gap-2">
                    <a class="btn btn-primary" href="{{ request()->getBaseUrl() }}/admin/login">Admin Login</a>
                    <a class="btn btn-outline-primary" href="{{ request()->getBaseUrl() }}/seller/login">Seller Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
