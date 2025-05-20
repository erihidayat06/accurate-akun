<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HargaController extends Controller
{
    public function store(Request $request)
    {

        $token = Auth::user()->accurateToken;

        if (!$token || !$token->access_token) {
            Log::error('Token tidak ditemukan untuk user: ' . Auth::id());
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        $accessToken = $token->access_token;

        $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
            Log::error('Gagal buka DB Accurate', ['response' => $openDbResponse->json()]);
            return back()->with('error', 'Gagal membuka database Accurate.');
        }

        $sessionId = $openDbResponse['session'];
        $host = $openDbResponse['host']; // GUNAKAN host ini



        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
        ])->asForm()->post($host . '/accurate/api/item/save.do', [
            'name' => $request->nama_produk,
            'id' => $request->id,
            'no' => $request->no,
            'itemType' => $request->itemType, // ✅ Tambahkan ini!
            'unitPrice' => $request->unitPrice,
            'unit2Price' => $request->unit2Price,
            'unit3Price' => $request->unit3Price,
            'unit4Price' => $request->unit4Price,
            'unit5Price' => $request->unit5Price,
        ]);




        // DEBUG DENGAN DETAIL
        Log::debug('Response status:', ['status' => $response->status()]);


        if ($response->successful() && ($response['s'] ?? false)) {
            Cache::flush();
            return back()->with('success', 'Produk berhasil disimpan ke Accurate.');
        } else {
            Log::error('Gagal simpan item ke Accurate', [
                'request' => $request->all(),
                'status' => $response->status(),

            ]);

            $error = $response['error'] ?? 'Terjadi kesalahan saat menyimpan item.';
            return back()->with('error', 'Gagal simpan: ' . $error);
        }
    }
}
