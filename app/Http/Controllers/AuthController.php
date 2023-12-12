<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    public function Register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|min:11',
            'role' => 'required|in:admin,cashier',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $pegawai = Pegawai::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
            'role' => $request->role,
        ]);

        return response(['message' => 'Registration successful'], 201);
    }

    public function Login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $pegawai = Auth::user();
            $token = $pegawai->createToken('api-token')->plainTextToken;

            $roleSpecificDashboard = $this->getDashboardRoute($pegawai->role);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'dashboard_url' => $roleSpecificDashboard,
                'user_data' => $this->getUserData($pegawai),
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    private function getDashboardRoute($role)
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard'; // Customize the admin dashboard route
                break;
            case 'cashier':
                return '/cashier/dashboard'; // Customize the cashier dashboard route
                break;
            default:
                return '/'; // Default route for unknown roles
        }
    }

    private function getUserData($pegawai)
    {
        return [
            'name' => $pegawai->name,
            'email' => $pegawai->email,
            'phone_number' => $pegawai->phone_number, // Adjust this line based on your actual column name
            'role' => $pegawai->role,
        ];
    }
}
