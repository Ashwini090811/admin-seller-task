<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UiController extends Controller
{
    private function dispatchApi(string $method, string $uri, array $data = [], ?string $token = null): Response
    {
        $server = ['HTTP_ACCEPT' => 'application/json'];

        if ($token) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $content = null;
        $payload = $method === 'GET' ? $data : [];

        if ($method !== 'GET') {
            $server['CONTENT_TYPE'] = 'application/json';
            $content = json_encode($data);
        }

        $request = Request::create($uri, $method, $payload, [], [], $server, $content);

        return app(Kernel::class)->handle($request);
    }

    private function decodeJson(Response $response): array
    {
        $decoded = json_decode($response->getContent(), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function baseUrl(Request $request): string
    {
        return rtrim($request->getSchemeAndHttpHost() . $request->getBaseUrl(), '/');
    }

    public function home()
    {
        return view('home');
    }

    public function adminLoginForm()
    {
        return view('auth.admin_login');
    }

    public function sellerLoginForm()
    {
        return view('auth.seller_login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $response = $this->dispatchApi('POST', '/api/admin/login', $request->only('email', 'password'));
        $status = $response->getStatusCode();
        $payload = $this->decodeJson($response);

        if ($status >= 200 && $status < 300) {
            Session::put('admin_token', $payload['token'] ?? null);
            return redirect()->to($this->baseUrl($request) . '/admin/sellers');
        }

        if ($status === 401) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        if ($status === 422) {
            $errors = $payload['errors'] ?? [];
            return back()->withErrors($errors)->withInput();
        }

        return back()->withErrors(['email' => 'Login failed. Try again.'])->withInput();
    }

    public function sellerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $response = $this->dispatchApi('POST', '/api/seller/login', $request->only('email', 'password'));
        $status = $response->getStatusCode();
        $payload = $this->decodeJson($response);

        if ($status >= 200 && $status < 300) {
            Session::put('seller_token', $payload['token'] ?? null);
            return redirect()->to($this->baseUrl($request) . '/seller/products');
        }

        if ($status === 401) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        if ($status === 422) {
            $errors = $payload['errors'] ?? [];
            return back()->withErrors($errors)->withInput();
        }

        return back()->withErrors(['email' => 'Login failed. Try again.'])->withInput();
    }

    public function adminLogout()
    {
        Session::forget('admin_token');
        return redirect()->to($this->baseUrl(request()) . '/admin/login');
    }

    public function sellerLogout()
    {
        Session::forget('seller_token');
        return redirect()->to($this->baseUrl(request()) . '/seller/login');
    }

    public function adminSellers(Request $request)
    {
        $token = Session::get('admin_token');
        if (!$token) {
            return redirect()->route('admin.login.form');
        }

        $response = $this->dispatchApi('GET', '/api/admin/sellers', ['per_page' => 10], $token);
        $status = $response->getStatusCode();
        $payload = $this->decodeJson($response);

        $sellers = $status >= 200 && $status < 300 ? ($payload['data'] ?? []) : [];
        $error = $status >= 200 && $status < 300 ? null : ($payload['message'] ?? 'Failed to fetch sellers.');

        return view('admin.sellers', compact('sellers', 'error'));
    }

    public function createSeller(Request $request)
    {
        $token = Session::get('admin_token');
        if (!$token) {
            return redirect()->route('admin.login.form');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'skills' => 'required|string',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:password'
        ]);

        $skills = array_values(array_filter(array_map('trim', explode(',', $request->input('skills')))));

        $payload = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'mobile' => $request->input('mobile'),
            'country' => $request->input('country'),
            'state' => $request->input('state'),
            'skills' => $skills,
            'password' => $request->input('password')
        ];

        $response = $this->dispatchApi('POST', '/api/admin/sellers', $payload, $token);
        $status = $response->getStatusCode();
        $responsePayload = $this->decodeJson($response);

        if ($status === 201) {
            return back()->with('success', 'Seller created successfully.');
        }

        if ($status === 422) {
            $errors = $responsePayload['errors'] ?? [];
            return back()->withErrors($errors)->withInput();
        }

        return back()->withErrors(['form' => $responsePayload['message'] ?? 'Failed to create seller.'])->withInput();
    }

    public function sellerProducts(Request $request)
    {
        $token = Session::get('seller_token');
        if (!$token) {
            return redirect()->route('seller.login.form');
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $response = $this->dispatchApi('GET', '/api/seller/products', [
            'per_page' => $perPage,
            'page' => $page
        ], $token);
        $status = $response->getStatusCode();
        $payload = $this->decodeJson($response);

        $products = $status >= 200 && $status < 300 ? ($payload['data'] ?? []) : [];
        $error = $status >= 200 && $status < 300 ? null : ($payload['message'] ?? 'Failed to fetch products.');
        $pagination = null;

        if ($status >= 200 && $status < 300) {
            $baseUrl = $this->baseUrl($request);
            $currentPage = (int) ($payload['current_page'] ?? $page);
            $lastPage = (int) ($payload['last_page'] ?? 1);
            $total = (int) ($payload['total'] ?? count($products));

            $pagination = [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'prev_url' => $currentPage > 1
                    ? $baseUrl . '/seller/products?page=' . ($currentPage - 1) . '&per_page=' . $perPage
                    : null,
                'next_url' => $currentPage < $lastPage
                    ? $baseUrl . '/seller/products?page=' . ($currentPage + 1) . '&per_page=' . $perPage
                    : null,
                'page_urls' => []
            ];

            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
            for ($i = $start; $i <= $end; $i++) {
                $pagination['page_urls'][] = [
                    'page' => $i,
                    'url' => $baseUrl . '/seller/products?page=' . $i . '&per_page=' . $perPage,
                    'is_current' => $i === $currentPage
                ];
            }
        }

        return view('seller.products', compact('products', 'error', 'pagination'));
    }

    public function createProduct(Request $request)
    {
        $token = Session::get('seller_token');
        if (!$token) {
            return redirect()->route('seller.login.form');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand1_name' => 'required|string|max:255',
            'brand1_price' => 'required|numeric|min:0',
            'brand1_detail' => 'nullable|string',
            'brand1_image' => 'nullable|image|max:2048',
            'brand2_name' => 'nullable|string|max:255',
            'brand2_price' => 'nullable|numeric|min:0',
            'brand2_detail' => 'nullable|string',
            'brand2_image' => 'nullable|image|max:2048'
        ]);

        $brand1ImageUrl = $this->storeBrandImage($request, 'brand1_image');
        $brand2ImageUrl = $this->storeBrandImage($request, 'brand2_image');

        $brands = [];

        if ($request->filled('brand1_name') || $request->filled('brand1_price')) {
            $brands[] = [
                'name' => $request->input('brand1_name'),
                'detail' => $request->input('brand1_detail'),
                'image' => $brand1ImageUrl,
                'price' => (float) $request->input('brand1_price')
            ];
        }

        if ($request->filled('brand2_name') || $request->filled('brand2_price')) {
            $brands[] = [
                'name' => $request->input('brand2_name'),
                'detail' => $request->input('brand2_detail'),
                'image' => $brand2ImageUrl,
                'price' => (float) $request->input('brand2_price')
            ];
        }

        $payload = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'brands' => $brands
        ];

        $response = $this->dispatchApi('POST', '/api/seller/products', $payload, $token);
        $status = $response->getStatusCode();
        $responsePayload = $this->decodeJson($response);

        if ($status === 201) {
            return back()->with('success', 'Product created successfully.');
        }

        if ($status === 422) {
            $errors = $responsePayload['errors'] ?? [];
            $mapped = $this->mapBrandErrors($errors);
            return back()->withErrors($mapped)->withInput();
        }

        return back()->withErrors(['form' => $responsePayload['message'] ?? 'Failed to create product.'])->withInput();
    }

    private function storeBrandImage(Request $request, string $field): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $path = $request->file($field)->store('brands', 'public');

        return Storage::url($path);
    }

    public function downloadProductPdf(int $productId)
    {
        $token = Session::get('seller_token');
        if (!$token) {
            return redirect()->route('seller.login.form');
        }

        $response = $this->dispatchApi('GET', "/api/seller/products/{$productId}/pdf", [], $token);
        $status = $response->getStatusCode();

        if ($status >= 200 && $status < 300) {
            if ($response instanceof StreamedResponse) {
                return $response;
            }

            return response($response->getContent(), $status)
                ->header('Content-Type', $response->headers->get('Content-Type', 'application/pdf'))
                ->header('Content-Disposition', 'inline; filename="product-' . $productId . '.pdf"');
        }

        $payload = $this->decodeJson($response);
        $message = $payload['message'] ?? 'Failed to view PDF.';

        return back()->withErrors(['form' => $message]);
    }

    public function deleteProduct(int $productId)
    {
        $token = Session::get('seller_token');
        if (!$token) {
            return redirect()->route('seller.login.form');
        }

        $response = $this->dispatchApi('DELETE', "/api/seller/products/{$productId}", [], $token);
        $status = $response->getStatusCode();

        if ($status >= 200 && $status < 300) {
            return back()->with('success', 'Product deleted successfully.');
        }

        $payload = $this->decodeJson($response);
        $message = $payload['message'] ?? 'Failed to delete product.';

        return back()->withErrors(['form' => $message]);
    }

    private function mapBrandErrors(array $errors): array
    {
        $mapped = $errors;

        foreach ($errors as $key => $messages) {
            if (str_starts_with($key, 'brands.0.')) {
                $mapped[str_replace('brands.0.', 'brand1_', $key)] = $messages;
            }

            if (str_starts_with($key, 'brands.1.')) {
                $mapped[str_replace('brands.1.', 'brand2_', $key)] = $messages;
            }
        }

        return $mapped;
    }
}
