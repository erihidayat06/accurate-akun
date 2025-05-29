<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HargaController extends Controller
{


    public function store(Request $request)
    {

        $token = Auth::user()->accurateToken;
        if (!$token || !$token->access_token) {
            return response()->json(['success' => false, 'message' => 'Access token tidak ditemukan.']);
        }

        $accessToken = $token->access_token;

        $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
            return response()->json(['success' => false, 'message' => 'Gagal mendapatkan session dari Accurate.']);
        }

        $sessionId = $openDbResponse['session'];
        $host = $openDbResponse['host'];

        $request->validate([
            'itemNo' => 'required|string',
            'price' => 'required|numeric|min:0',
            'transDate' => 'required|date',
            'description' => 'nullable|string|max:255',
            'salesAdjustmentCategoryList' => 'required|array|min:1',
        ]);

        $payload = [
            'salesAdjustmentType' => 'ITEM_PRICE_TYPE',
            'transDate' => Carbon::parse($request->transDate)->format('d/m/Y'),
            'description' => $request->description,
            'salesAdjustmentCategoryList' => array_map(fn($id) => ['id' => (int)$id], $request->salesAdjustmentCategoryList),
            'detailItem' => [
                [
                    'itemUnitName' => $request->st,
                    'itemNo' => $request->itemNo,
                    'price' => (float) $request->price,
                ]
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($host . '/accurate/api/sellingprice-adjustment/save.do', $payload);

        if ($response->successful() && $response['s'] === true) {
            return response()->json(['success' => true, 'message' => $response['d'][0] ?? 'Penyesuaian berhasil.']);
        } else {
            return response()->json([
                'success' => false,
                'message' => $response['d'][0] ?? 'Penyesuaian gagal dikirim.',
                'debug' => $response->json(),
            ]);
        }
    }



    public function showImportForm()
    {
        // Misalnya ambil list kategori
        $categories = [
            (object)['id' => 1, 'name' => 'Kategori A'],
            (object)['id' => 2, 'name' => 'Kategori B'],
        ];

        return view('penyesuaian.import', compact('categories'));
    }



    public function import(Request $request)
    {
        $request->validate([
            'transDate' => 'required|date',
            'description' => 'nullable|string|max:255',
            'salesAdjustmentCategoryList' => 'required|array|min:1',
            'file' => 'required|file|mimes:xls,xlsx',
            'dbId' => 'required|string',
        ]);

        $token = Auth::user()->accurateToken;
        if (!$token || !$token->access_token) {
            return response()->json(['success' => false, 'message' => 'Access token tidak ditemukan.']);
        }

        $accessToken = $token->access_token;

        $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);
        if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
            return response()->json(['success' => false, 'message' => 'Gagal mendapatkan session dari Accurate.']);
        }

        $sessionId = $openDbResponse['session'];
        $host = $openDbResponse['host'];

        // 🔁 Parse Excel menggunakan PhpSpreadsheet
        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $detailItems = [];
        foreach ($rows as $i => $row) {
            if ($i == 0) continue; // Skip header
            if (!isset($row[0], $row[1], $row[2])) continue;

            $detailItems[] = [
                'itemNo' => $row[0],       // e.g. 'ITEM001'
                'price' => (float) $row[2], // e.g. 10000
                'itemUnitName' => $row[3], // e.g. 'pcs'
            ];
        }

        if (empty($detailItems)) {
            return response()->json(['success' => false, 'message' => 'Data Excel kosong atau format tidak sesuai.']);
        }

        $payload = [
            'salesAdjustmentType' => 'ITEM_PRICE_TYPE',
            'transDate' => Carbon::parse($request->transDate)->format('d/m/Y'),
            'description' => $request->description,
            'salesAdjustmentCategoryList' => array_map(fn($id) => ['id' => (int)$id], $request->salesAdjustmentCategoryList),
            'detailItem' => $detailItems,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($host . '/accurate/api/sellingprice-adjustment/save.do', $payload);

        if ($response->successful() && $response['s'] === true) {
            return response()->json(['success' => true, 'message' => $response['d'][0] ?? 'Penyesuaian berhasil.']);
        } else {
            return response()->json([
                'success' => false,
                'message' => $response['d'][0] ?? 'Penyesuaian gagal dikirim.',
                'debug' => $response->json(),
            ]);
        }
    }




















    // public function store(Request $request)
    // {

    //     $token = Auth::user()->accurateToken;

    //     if (!$token || !$token->access_token) {
    //         Log::error('Token tidak ditemukan untuk user: ' . Auth::id());
    //         return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
    //     }

    //     $accessToken = $token->access_token;

    //     $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
    //         'id' => $request->dbId,
    //     ]);

    //     if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
    //         Log::error('Gagal buka DB Accurate', ['response' => $openDbResponse->json()]);
    //         return back()->with('error', 'Gagal membuka database Accurate.');
    //     }

    //     $sessionId = $openDbResponse['session'];
    //     $host = $openDbResponse['host']; // GUNAKAN host ini



    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $accessToken,
    //         'X-Session-ID' => $sessionId,
    //     ])->asForm()->post($host . '/accurate/api/item/save.do', [
    //         'name' => $request->nama_produk,
    //         'id' => $request->id,
    //         'no' => $request->no,
    //         'itemType' => $request->itemType, // ✅ Tambahkan ini!
    //         'unitPrice' => $request->unitPrice,
    //         'unit2Price' => $request->unit2Price,
    //         'unit3Price' => $request->unit3Price,
    //         'unit4Price' => $request->unit4Price,
    //         'unit5Price' => $request->unit5Price,
    //     ]);




    //     // DEBUG DENGAN DETAIL
    //     Log::debug('Response status:', ['status' => $response->status()]);


    //     if ($response->successful() && ($response['s'] ?? false)) {
    //         Cache::flush();
    //         return back()->with('success', 'Produk berhasil disimpan ke Accurate.');
    //     } else {
    //         Log::error('Gagal simpan item ke Accurate', [
    //             'request' => $request->all(),
    //             'status' => $response->status(),

    //         ]);

    //         $error = $response['error'] ?? 'Terjadi kesalahan saat menyimpan item.';
    //         return back()->with('error', 'Gagal simpan: ' . $error);
    //     }
    // }
}
