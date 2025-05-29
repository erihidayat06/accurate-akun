<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccurateToken;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccurateCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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

            $this->clientId = config('services.accurate.client_id');
            $this->clientSecret = config('services.accurate.client_secret');
            $this->redirectUri = config('services.accurate.redirect');

            return $next($request);
        });
    }



    public function redirectToAccurate()
    {

        $url = "https://account.accurate.id/oauth/authorize?" . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            'scope'         => 'item_view item_adjustment_save purchase_invoice_view purchase_order_view purchase_payment_view customer_view price_category_view sellingprice_adjustment_save',
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
        $user = Auth::user();

        AccurateToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            ]
        );

        Log::info('Access and refresh tokens stored in session', ['access_token' => $data['access_token']]);

        return redirect('/accurate/database')->with('success', 'Berhasil terhubung dengan Accurate.');
    }



    public function getDb()
    {
        $token = Auth::user()->accurateToken;
        if (!$token || !$token->access_token) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }


        $accessToken = $token->access_token;
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
        $token = Auth::user()->accurateToken;
        if (!$token || !$token->access_token) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        $accessToken = $token->access_token;

        // Step 1: Buka database Accurate
        $openDbResponse = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDbResponse->ok() || !isset($openDbResponse['session'], $openDbResponse['host'])) {
            return response()->json(['error' => 'Session ID atau Host tidak ditemukan dari Accurate.']);
        }

        $sessionId = $openDbResponse['session'];
        $host = $openDbResponse['host'];

        // Ambil nomor halaman dari query parameter
        $page = $request->query('page', 1); // default ke 1
        $keyword = $request->input('keyword', 1); // Ambil data pencarian
        $pageSize = 10;

        // Step 2: Ambil daftar item
        $itemListResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
        ])->get($host . '/accurate/api/item/list.do', [
            'fields' => 'id,name,no',
            'filter.itemType' => 'INVENTORY',
            'sp.page' => $page,
            'sp.pageSize' => $pageSize,
            'sp.sort' => 'name|asc',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val' => $keyword,
        ]);

        if (!$itemListResponse->ok()) {
            return response()->json(['error' => 'Gagal mengambil data barang dari Accurate.']);
        }

        $itemsRaw = $itemListResponse['d'] ?? [];
        $pagination = $itemListResponse['sp'] ?? [];
        $totalPages = $pagination['pageCount'] ?? 1;

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

            $unit1Name = $item['unit1Name'] ?? 'PCS';
            $hargaSatuan[$unit1Name] = $item['unitPrice'] ?? 0;

            for ($i = 2; $i <= 5; $i++) {
                $unitName = $item["unit{$i}Name"] ?? "UNIT{$i}";
                $unitPrice = $item["unit{$i}Price"] ?? 0;
                if ($unitPrice !== null) {
                    $hargaSatuan[$unitName] = $unitPrice;
                }
            }

            $itemBranchName = $item['itemBranchName'] ?? '[Semua Cabang]';
            $itemBranchName = str_replace(['[', ']'], '', $itemBranchName);
            $itemCategory = $item['itemCategory']['name'] ?? 'Tidak ada kategori';

            return [
                'nama' => $item['name'] ?? '-',
                'upcNo' => $item['upcNo'] ?? '-',
                'kode' => $item['no'] ?? '-',
                'harga_satuan' => $hargaSatuan,
                'cabang' => $itemBranchName,
                'kategori' => $itemCategory,
            ];
        });



        return view('accurate.getItem', [
            'items' => $produkList,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'dbId' => $request->dbId,
        ]);
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
        $token = Auth::user()->accurateToken;
        if (!$token || !$token->access_token) {
            return redirect('/accurate/login')->with('error', 'Access token tidak ditemukan.');
        }

        $accessToken = $token->access_token;
        $currentPage = $request->input('page', 1);
        $startDate = Carbon::parse($request->start_date ?? Carbon::now()->startOfMonth())->format('d/m/Y');
        $endDate = Carbon::parse($request->end_date ?? Carbon::now())->format('d/m/Y');

        $openDb = Http::withToken($accessToken)->get('https://account.accurate.id/api/open-db.do', [
            'id' => $request->dbId,
        ]);

        if (!$openDb->ok() || !isset($openDb['session'], $openDb['host'])) {
            return response()->json(['error' => 'Gagal membuka database Accurate']);
        }

        $sessionId = $openDb['session'];
        $host = $openDb['host'];

        $kategori_penjualan = [];
        if (!empty($accessToken) && !empty($sessionId)) {
            $endpoint = $host . '/accurate/api/price-category/list.do';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Session-ID' => $sessionId,
            ])->get($endpoint);

            if ($response->successful()) {
                $list = $response->json('d') ?? [];
                $kategori_penjualan = collect($list)->map(function ($item) use ($accessToken, $sessionId, $host) {
                    $detail = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'X-Session-ID' => $sessionId,
                    ])->get($host . '/accurate/api/price-category/detail.do', [
                        'id' => $item['id'],
                    ]);
                    return $detail->successful() ? $detail->json('d') : $item;
                })->all();
            }
        }

        $itemList = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID' => $sessionId,
        ])->get($host . '/accurate/api/purchase-order/list.do', [
            'sp.pageSize' => 99999,
            'sp.page' => $currentPage,
            'filter.transDate.op' => 'BETWEEN',
            'filter.transDate.val[0]' => $startDate,
            'filter.transDate.val[1]' => $endDate,
        ]);

        $itemsRaw = $itemList['d'] ?? [];
        $pagination = $itemList['sp'] ?? [];
        $totalPages = $pagination['pageCount'] ?? 1;

        $cacheKey = 'analisaHargaTerakhir_' . md5("{$request->dbId}_{$startDate}_{$endDate}");

        if ($request->input('reset')) {
            Cache::forget($cacheKey);
        }

        $hasil = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($itemsRaw, $accessToken, $sessionId, $host) {
            $result = [];

            foreach ($itemsRaw as $item) {
                $detail = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-Session-ID' => $sessionId,
                ])->get($host . '/accurate/api/purchase-order/detail.do', [
                    'id' => $item['id'],
                    'sp.sort' => 'asc',
                ]);

                if (!$detail->ok() || !isset($detail['d']['detailItem'])) {
                    continue;
                }

                $transDate = $detail['d']['transDateView'] ?? '-';

                foreach ($detail['d']['detailItem'] as $detailItem) {
                    $d = $detailItem['item'] ?? null;
                    if (!$d) continue;

                    $row = [
                        'no' => $d['no'] ?? '-',
                        'id' => $d['id'] ?? '-',
                        'nama_barang' => $d['name'] ?? '-',
                        'qty' => $detailItem['quantity'] ?? 0,
                        'st' => $detailItem['availableItemUnitName'] ?? '-',
                        'hb_baru' => $detailItem['unitPrice'] ?? 0,
                        'disc_fp_baru' => $detailItem['discount'] ?? 0,
                        'hb_lama' => $d['unit1VendorPrice'] ?? 0,
                        'itemType' => $d['itemType'] ?? 0,
                        'tgl_transaksi' => $transDate,
                    ];

                    for ($i = 1; $i <= 5; $i++) {
                        $unit = $d["unit{$i}"] ?? null;
                        $unitName = $unit['name'] ?? null;
                        $hj_lama = $d["unitPrice"] ?? 0;
                        $hj_baru = $i === 1 ? ($d["unitPrice"] ?? 0) : ($d["unit{$i}Price"] ?? 0);
                        $ratio = $d["ratio{$i}"] ?? null;

                        if ($unitName) {
                            $row["unit{$i}Name"] = "unit{$i}Name";
                            $row["hj_lama{$i}"] = $hj_lama;
                            $row["hj_baru{$i}"] = $hj_baru;
                            $row["st{$i}"] = $unitName;
                            $row["rs{$i}"] = $ratio;
                        }
                    }

                    $result[] = $row;
                }
            }

            usort($result, function ($a, $b) {
                return strtotime($a['tgl_transaksi']) <=> strtotime($b['tgl_transaksi']);
            });

            return $result;
        });

        return view('accurate.analisaHargaTerakhir', [
            'hasil' => $hasil,
            'dbId' => $request->dbId,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'kategori_penjualan' => $kategori_penjualan
        ]);
    }




    public function login()
    {
        return view('accurate.login');
    }
}
