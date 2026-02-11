<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    public function addProduct(Request $request)
    {
        if (!auth()->user() instanceof Seller) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'brands' => 'required|array|min:1',
                'brands.*.name' => 'required|string|max:255',
                'brands.*.detail' => 'nullable|string',
                'brands.*.image' => 'nullable|string',
                'brands.*.price' => 'required|numeric|min:0'
            ]);

            $product = Product::create([
                'seller_id' => auth()->id(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'brands' => $data['brands']
            ]);

            return response()->json([
                'message' => 'Product created',
                'product' => $product
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function listProducts(Request $request)
    {
        if (!auth()->user() instanceof Seller) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $perPage = $request->integer('per_page', 10);

        $products = Product::query()
            ->where('seller_id', auth()->id())
            ->paginate($perPage);

        return response()->json($products);
    }

    public function viewPdf(int $productId)
    {
        if (!auth()->user() instanceof Seller) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            return response()->json([
                'message' => 'PDF generator not installed. Install barryvdh/laravel-dompdf.'
            ], 500);
        }

        try {
            $product = Product::query()
                ->where('seller_id', auth()->id())
                ->findOrFail($productId);

            $brands = $product->brands ?? [];
            $totalPrice = collect($brands)->sum('price');

            $pdf = Pdf::loadView('product_pdf', [
                'product' => $product,
                'brands' => $brands,
                'totalPrice' => $totalPrice
            ]);

            $response = $pdf->stream('product-' . $product->id . '.pdf');

            return $response
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Failed to generate PDF',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function deleteProduct(int $productId)
    {
        if (!auth()->user() instanceof Seller) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($productId < 1) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['productId' => ['The product id must be a positive integer.']]
            ], 422);
        }

        try {
            $product = Product::query()
                ->where('seller_id', auth()->id())
                ->findOrFail($productId);

            $product->delete();

            return response()->json(['message' => 'Product deleted']);
        } catch (ModelNotFoundException $exception) {
            return response()->json(['message' => 'Product not found'], 404);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
