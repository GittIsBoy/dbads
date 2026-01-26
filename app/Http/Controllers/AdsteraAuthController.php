<?php

namespace App\Http\Controllers;

use App\Services\AdsteraAuthService;
use App\Models\AdsteraSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdsteraAuthController extends Controller
{
    protected AdsteraAuthService $service;

    public function __construct(AdsteraAuthService $service)
    {
        $this->service = $service;
    }
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // Adstera uses API keys (X-API-Key). There is no username/password login via API.
            // We'll verify the configured API key and if valid create/find a local user using provided username as email.
            $verify = $this->service->verifyKey();

            // create or find local user by provided username (treated as email)
            $email = $data['username'];
            $user = User::firstWhere('email', $email);
            if (!$user) {
                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => Str::random(32),
                ]);
            }

            // store session using API key as access_token; adstera_user_id not provided by API key flow so use hash
            $apiKey = config('adstera.api_key');
            $adsteraUserId = 'key:' . substr(md5($apiKey), 0, 12);

            $session = AdsteraSession::updateOrCreate(
                ['adstera_user_id' => $adsteraUserId],
                [
                    'user_id' => $user->id,
                    'access_token' => $apiKey,
                    'refresh_token' => null,
                    'expires_at' => null,
                ]
            );

            return response()->json(['ok' => true, 'adstera_user_id' => $adsteraUserId, 'session_id' => $session->id, 'verify_sample' => $verify]);
        } catch (\Exception $e) {
            Log::error('Adstera verify error: '.$e->getMessage());
            return response()->json(['message' => 'Adstera verify failed', 'error' => $e->getMessage()], 401);
        }
    }

    public function refresh(Request $request)
    {
        $data = $request->validate(['refresh_token' => 'required|string']);

        try {
            $result = $this->service->refreshToken($data['refresh_token']);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Adstera refresh error: '.$e->getMessage());
            return response()->json(['message' => 'Refresh failed'], 401);
        }
    }

    public function domains(Request $request)
    {
        try {
            $res = $this->service->request('GET', 'domains.json', ['query' => ['format' => 'json']]);
            $items = $res['items'] ?? $res['data'] ?? [];
            return response()->json(['items' => $items]);
        } catch (\Exception $e) {
            Log::error('Adstera domains error: '.$e->getMessage());
            return response()->json(['items' => []], 500);
        }
    }

    public function placements(Request $request)
    {
        try {
            $res = $this->service->request('GET', 'placements.json', ['query' => ['format' => 'json']]);
            $items = $res['items'] ?? $res['data'] ?? [];
            return response()->json(['items' => $items]);
        } catch (\Exception $e) {
            Log::error('Adstera placements error: '.$e->getMessage());
            return response()->json(['items' => []], 500);
        }
    }

    public function domainPlacements(Request $request, $domainId)
    {
        try {
            $res = $this->service->request('GET', "domain/{$domainId}/placements.json", ['query' => ['format' => 'json']]);
            $items = $res['items'] ?? $res['data'] ?? [];
            return response()->json(['items' => $items]);
        } catch (\Exception $e) {
            Log::error('Adstera domain placements error: '.$e->getMessage());
            return response()->json(['items' => []], 500);
        }
    }

    // helper to map adstera response and persist session
    private function processAdsteraResult(array $result)
    {
        $adsteraUserId = $result['data']['id'] ?? $result['user']['id'] ?? $result['id'] ?? $result['user_id'] ?? null;
        $accessToken = $result['access_token'] ?? $result['token'] ?? $result['data']['access_token'] ?? null;
        $refreshToken = $result['refresh_token'] ?? $result['data']['refresh_token'] ?? null;
        $expiresIn = $result['expires_in'] ?? $result['expires'] ?? null;

        if (empty($adsteraUserId) && isset($result['data']['user_id'])) {
            $adsteraUserId = $result['data']['user_id'];
        }

        if (empty($adsteraUserId)) {
            return response()->json($result);
        }

        $email = $result['data']['email'] ?? $result['user']['email'] ?? $result['email'] ?? null;
        $localUserId = null;
        if (!empty($email)) {
            $user = User::firstWhere('email', $email);
            if (!$user) {
                $user = User::create([
                    'name' => $result['data']['name'] ?? $result['user']['name'] ?? ($email),
                    'email' => $email,
                    'password' => Str::random(32),
                ]);
            }
            $localUserId = $user->id;
        }

        $session = AdsteraSession::updateOrCreate(
            ['adstera_user_id' => (string)$adsteraUserId],
            [
                'user_id' => $localUserId,
                'access_token' => $accessToken ?? '',
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresIn ? now()->addSeconds((int)$expiresIn) : null,
            ]
        );

        return response()->json(['ok' => true, 'adstera_user_id' => $adsteraUserId, 'session_id' => $session->id, 'raw' => $result]);
    }
}
