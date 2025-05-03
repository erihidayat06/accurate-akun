<?php

namespace App\Services;

use App\Models\AccurateToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AccurateService
{
    public function getValidToken(): AccurateToken
    {
        $token = AccurateToken::latest()->first();

        if (!$token) {
            throw new \Exception("Token Accurate belum tersedia. Silakan login terlebih dahulu.");
        }

        if (Carbon::now()->greaterThanOrEqualTo($token->token_expires_at)) {
            $response = Http::asForm()->post('https://account.accurate.id/oauth/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $token->refresh_token,
                'client_id'     => config('services.accurate.client_id'),
                'client_secret' => config('services.accurate.client_secret'),
            ]);

            $data = $response->json();

            if (!isset($data['access_token'])) {
                throw new \Exception("Gagal refresh token Accurate: " . json_encode($data));
            }

            $token->update([
                'access_token'     => $data['access_token'],
                'refresh_token'    => $data['refresh_token'],
                'token_expires_at' => Carbon::now()->addSeconds($data['expires_in']),
            ]);
        }

        return $token;
    }

    public function request(string $method, string $endpoint, array $params = [])
    {
        $token = $this->getValidToken();

        $http = Http::withToken($token->access_token)
            ->withHeaders([
                'X-Session-ID' => $token->session_db,
            ]);

        $url = 'https://public.accurate.id/api/' . ltrim($endpoint, '/');

        $response = $http->$method($url, $params);

        if ($response->failed()) {
            throw new \Exception("Request ke Accurate gagal: " . $response->body());
        }

        return $response->json();
    }
}
