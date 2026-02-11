<?php 
namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function createSeller(Request $request)
    {
        if (!auth()->user() instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:sellers,email',
                'mobile' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'skills' => 'required|array|min:1',
                'skills.*' => 'string|max:100',
                'password' => 'required|string|min:6'
            ]);

            $data['password'] = Hash::make($data['password']);

            $seller = Seller::create($data);

            return response()->json([
                'message' => 'Seller created',
                'seller' => $seller
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Failed to create seller',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function listSellers(Request $request)
    {
        if (!auth()->user() instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $perPage = $request->integer('per_page', 10);

        $sellers = Seller::query()->paginate($perPage);

        return response()->json($sellers);
    }
}