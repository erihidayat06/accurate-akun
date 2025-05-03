<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccurateCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AccurateController extends Controller
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->middleware('auth'); // Pastikan user tersedia

        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $credential = $user->accurate;

            if (!$credential) {
                abort(403, 'Accurate credentials not found for this user.');
            }

            $this->clientId = $credential->client_id;
            $this->clientSecret = $credential->client_secret;
            $this->redirectUri = $credential->redirect_uri;

            return $next($request);
        });
    }



    public function redirectToAccurate()
    {
        $url = "https://account.accurate.id/oauth/authorize?" . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            'scope'         => 'item_view item_save sales_invoice_view',
        ]);

        Log::info('Redirecting to Accurate login', ['url' => $url]);

        return redirect($url);
    }

    public function handleCallback(Request $request)
    {
        Log::info('Callback full URL after authorization', ['full_url' => $request->fullUrl()]);
        Log::info('Accurate callback received', ['query' => $request->query()]);

        if ($request->has('error')) {
            $error = $request->query('error');
            $errorDescription = $request->query('error_description');

            Log::error('Accurate authorization error', [
                'error' => $error,
                'description' => $errorDescription,
            ]);

            if ($error === 'access_denied') {
                return redirect()->route('accurate.login')->with('error', 'Anda harus memberikan izin untuk mengakses akun Accurate Anda. Silakan coba lagi.');
            }

            return response()->json([
                'error' => $error,
                'description' => $errorDescription,
                'message' => 'Otorisasi ditolak oleh pengguna. Silakan coba lagi.',
            ], 400);
        }

        $code = $request->query('code');

        if (empty(trim($code))) {
            Log::error('Authorization code is empty', ['code' => $code]);
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan'], 400);
        }

        Log::info('Authorization code received', ['code' => $code]);

        $basicAuth = base64_encode($this->clientId . ':' . $this->clientSecret);

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . $basicAuth,
            ])
            ->post('https://account.accurate.id/oauth/token', [
                'code'         => $code,
                'grant_type'   => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ]);

        if (!$response->ok()) {
            $data = $response->json();
            Log::error('Failed to get token from Accurate', is_array($data) ? $data : ['raw' => $response->body(), 'status' => $response->status()]);

            return response()->json([
                'error'   => 'Gagal mendapatkan access token dari Accurate',
                'details' => $data,
            ], $response->status());
        }

        $data = $response->json();

        Session::put('access_token', $data['access_token']);
        Session::put('refresh_token', $data['refresh_token']);

        Log::info('Access and refresh tokens stored in session', ['access_token' => $data['access_token']]);

        return redirect('/accurate/database')->with('success', 'Berhasil terhubung dengan Accurate.');
    }



    public function getDb()
    {
        $accessToken = Session::get('access_token');
        if (!$accessToken) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        // Step 1: Get list of databases
        $dbList = Http::withToken($accessToken)->get('https://account.accurate.id/api/db-list.do');
        if (!$dbList->ok() || !isset($dbList['d'][0]['id'])) {
            return response()->json(['error' => 'Gagal mengambil daftar database']);
        }

        $databases = $dbList['d'];

        return view('accurate.getdb', compact('databases'));
    }



    public function getItems(Request $request)
    {
        $accessToken = Session::get('access_token');

        if (!$accessToken) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        // Step 1: Buka database Accurate
        $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
            return response()->json(['error' => 'Session ID atau Host tidak ditemukan dari Accurate.']);
        }

        $sessionId = $openDbResponse['session'];
        $host = $openDbResponse['host'];

        // Step 2: Ambil daftar item
        $itemListResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
        ])->get($host . '/accurate/api/item/list.do', [
            'fields' => 'id,name,no',
            'filter.itemType' => 'INVENTORY',
            'sp.page' => 1,
            'sp.pageSize' => 10,
            'sp.sort' => 'name|asc',
        ]);

        if (!$itemListResponse->ok()) {
            return response()->json(['error' => 'Gagal mengambil data barang dari Accurate.']);
        }

        $itemsRaw = $itemListResponse['d'] ?? [];
        $detailedItems = [];

        // Step 3: Ambil detail tiap item
        foreach ($itemsRaw as $item) {
            $detailResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Session-ID' => $sessionId,
            ])->get($host . '/accurate/api/item/detail.do', [
                'id' => $item['id'],
            ]);

            if ($detailResponse->ok() && isset($detailResponse['d'])) {
                $detailedItems[] = $detailResponse['d'];
            } else {
                // Jika gagal ambil detail, tetap simpan ID, name dan no saja
                $detailedItems[] = [
                    'id' => $item['id'],
                    'name' => $item['name'] ?? '-',
                    'no' => $item['no'] ?? '-',
                    'unitPrice' => 0,
                    'unit2Price' => 0,
                    'unit3Price' => 0,
                    'unit4Price' => 0,
                    'unit5Price' => 0,
                    'unit1Name' => 'PCS',
                    'unit2Name' => 'BOX',
                    'unit3Name' => 'BALL',
                    'unit4Name' => 'LUSIN',
                    'unit5Name' => 'KARTON',
                ];
            }
        }


        $produkList = collect($detailedItems)->map(function ($item) {
            $hargaSatuan = [];

            // Unit pertama (unitPrice)
            $unit1Name = $item['unit1Name'] ?? 'PCS';
            $hargaSatuan[$unit1Name] = $item['unitPrice'] ?? 0;

            // Unit 2 sampai 5
            for ($i = 2; $i <= 5; $i++) {
                $unitName = $item["unit{$i}Name"] ?? "UNIT{$i}";
                $unitPrice = $item["unit{$i}Price"] ?? 0;

                // Hanya tambahkan jika tidak null
                if ($unitPrice !== null) {
                    $hargaSatuan[$unitName] = $unitPrice;
                }
            }

            // Pastikan "Semua Cabang" tanpa kurung
            // Pastikan "Semua Cabang" tanpa kurung siku
            $itemBranchName = $item['itemBranchName'] ?? '[Semua Cabang]';
            $itemBranchName = str_replace(['[', ']'], '', $itemBranchName); // Menghapus tanda kurung siku
            $itemCategory = $item['itemCategory']['name'] ?? 'Tidak ada kategori';

            return [
                'nama' => $item['name'] ?? '-',
                'kode' => $item['no'] ?? '-',
                'harga_satuan' => $hargaSatuan,
                'cabang' => $itemBranchName,
                'kategori' => $itemCategory,
            ];
        });


        return view('accurate.getItem', ['items' => $produkList]);
    }


    public function printPDF(Request $request)
    {
        $encodedItems = $request->input('selected_products', []);

        if (empty($encodedItems)) {
            return back()->with('error', 'Tidak ada produk yang dipilih.');
        }

        // Decode data produk
        $produk = collect($encodedItems)->map(function ($encoded) {
            return json_decode(base64_decode($encoded), true);
        });

        // Load PDF view
        $pdf = Pdf::loadView('accurate.pdf.produk', ['produk' => $produk]);

        return $pdf->stream('produk-terpilih.pdf');
    }
    public function analisaHargaTerakhir(Request $request)
    {
        $accessToken = Session::get('access_token');
        if (!$accessToken) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        // 1. Buka database Accurate
        $openDb = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDb->ok() || !isset($openDb['session'], $openDb['host'])) {
            return response()->json(['error' => 'Gagal membuka database Accurate']);
        }

        $sessionId = $openDb['session'];
        $host = $openDb['host'];

        // 2. Ambil semua produk
        $itemList = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
        ])->get($host . '/accurate/api/item/list.do', [
            'fields' => 'id,name,no',
            'filter.itemType' => 'INVENTORY',
            'sp.pageSize' => 50,
            'sp.page' => 1,
            'sp.sort' => 'name|asc'
        ]);

        $itemsRaw = $itemList['d'] ?? [];

        $hasil = [];

        foreach ($itemsRaw as $item) {
            // 3. Ambil harga beli & jual terakhir produk ini
            $detail = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Session-ID' => $sessionId,
            ])->get($host . '/accurate/api/item/detail.do', [
                'id' => $item['id'],
            ]);

            if (!$detail->ok() || !isset($detail['d'])) {
                continue;
            }

            $d = $detail['d'];

            // 4. Ambil semua unit (sampai 5)
            $units = [];
            for ($i = 1; $i <= 5; $i++) {
                $unitName = $d["unit{$i}Name"] ?? null;
                if ($unitName) {
                    $units[$unitName] = [
                        'harga_beli_terakhir' => $d["lastBuyPrice"][$i - 1] ?? 0,
                        'harga_jual_terakhir' => $d["lastSellPrice"][$i - 1] ?? 0,
                    ];
                }
            }

            $hasil[] = [
                'nama' => $d['name'] ?? '-',
                'kode' => $d['no'] ?? '-',
                'units' => $units,
            ];
        }

        return view('accurate.analisaHargaTerakhir', compact('hasil'));
    }




    public function login()
    {
        return view('accurate.login');
    }
}
