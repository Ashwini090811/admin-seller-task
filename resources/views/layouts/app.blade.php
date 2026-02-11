<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laravel Task UI' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ request()->getBaseUrl() }}/">Laravel Task</a>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-secondary" href="{{ request()->getBaseUrl() }}/admin/login">Admin</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ request()->getBaseUrl() }}/seller/login">Seller</a>
        </div>
    </div>
</nav>

<main class="container pb-5">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->has('form'))
        <div class="alert alert-danger">{{ $errors->first('form') }}</div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
