<?php 

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function adminLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $admin = Admin::where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $admin->createToken('admin_token', ['role:admin'])->plainTextToken,
            'role' => 'admin'
        ]);
    }

    public function sellerLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $seller = Seller::where('email', $data['email'])->first();

        if (!$seller || !Hash::check($data['password'], $seller->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $seller->createToken('seller_token', ['role:seller'])->plainTextToken,
            'role' => 'seller'
        ]);
    }
}