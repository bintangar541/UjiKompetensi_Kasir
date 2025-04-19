<?php

namespace App\Http\Controllers;

use App\Models\detail_sales;
use App\Models\User;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class UserController extends Controller
{



    public function login()
    {
        return view('auth.login'); // View login-nya simpan di resources/views/auth/login.blade.php
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        return redirect()->back()->withErrors(['login' => 'Email atau password salah'])->withInput();
    }

    public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login'); // Pastikan route('login') ada
}


    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
            'password' => 'required'
        ]);

        if (User::where('email', $request->email)->exists()) {
            return redirect()->back()->withErrors(['email' => 'Email sudah digunakan.'])->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('user.index')->with('success', 'Berhasil Menambah User');
    }

    public function edit($id)
    {
        try {
            $item = User::findOrFail($id);
            return view('user.edit', compact('item'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('user.list')->with('error', 'User tidak ditemukan!');
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
        ]);

        $user = User::findOrFail($id);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        // dd($user);
        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui!');
    }


    public function destroy(User $User, $id)
    {
        User::where('id', $id)->delete();
        return redirect()->route('user.index')->with('success', 'Berhasil Hapus User');
    }

    public function export()
    {
        // Menambahkan export Excel dengan nama file users.xlsx
        return Excel::download(new UsersExport, 'users.xlsx');
    }


}
