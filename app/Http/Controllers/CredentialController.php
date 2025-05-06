<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccurateCredential;

class CredentialController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        // Cek atau buat data kredensial Accurate untuk user ini
        $credential = AccurateCredential::firstOrCreate(
            ['user_id' => $user->id],
            [
                'client_id'     => '',
                'client_secret' => '',
                'redirect_uri'  => '',
            ]
        );

        return view('dashboard', compact('credential'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',

        ]);

        $user = auth()->user();

        // Buat atau perbarui data AccurateCredential
        AccurateCredential::updateOrCreate(
            ['user_id' => $user->id],
            [
                'client_id' => $request->input('client_id'),
                'client_secret' => $request->input('client_secret'),
            ]
        );

        return back()->with('success', 'Data berhasil disimpan.');
    }
}
