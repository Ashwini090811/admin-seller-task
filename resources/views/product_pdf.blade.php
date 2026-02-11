<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .sub { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        .total { margin-top: 12px; font-weight: bold; }
        img { max-height: 60px; }
    </style>
</head>
<body>
    <h1>{{ $product->name }}</h1>
    <div class="sub">{{ $product->description }}</div>

    <table>
        <thead>
            <tr>
                <th>Brand Name</th>
                <th>Image</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($brands as $brand)
                <tr>
                    <td>{{ $brand['name'] ?? '' }}</td>
                    <td>
                        @if (!empty($brand['image']))
                            @php
                                $imageUrl = $brand['image'];
                                $imagePath = str_starts_with($imageUrl, '/storage/')
                                    ? public_path($imageUrl)
                                    : $imageUrl;
                            @endphp
                            <img src="{{ $imagePath }}" alt="brand image">
                        @endif
                    </td>
                    <td>{{ number_format((float) ($brand['price'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">Total Price: {{ number_format((float) $totalPrice, 2) }}</div>
</body>
</html>
