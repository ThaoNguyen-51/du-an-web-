<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GHNService
{
    protected string $baseUrl;
    protected string $token;
    protected int $shopId;

    public function __construct()
    {
        $this->baseUrl = config('services.ghn.base_url');
        $this->token = config('services.ghn.token') ?? '';
        $this->shopId = (int) config('services.ghn.shop_id', 0);
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withOptions(['verify' => filter_var(config('services.ghn.verify_ssl', false), FILTER_VALIDATE_BOOLEAN)])
            ->acceptJson()
            ->timeout(15)
            ->withHeaders([
                'Token' => $this->token,
                'ShopId' => $this->shopId,
                'Content-Type' => 'application/json',
            ]);
    }

    public function getProvinces(): array
    {
        return $this->cachedMasterData('provinces', fn () => $this->get('/master-data/province'));
    }

    public function getDistricts(int $provinceId): array
    {
        return $this->cachedMasterData("districts:{$provinceId}", fn () => $this->get('/master-data/district', ['province_id' => $provinceId]));
    }

    public function getWards(int $districtId): array
    {
        return $this->cachedMasterData("wards:{$districtId}", fn () => $this->get('/master-data/ward', ['district_id' => $districtId]));
    }

    public function calculateFee(array $params): array
    {
        return $this->post('/v2/shipping-order/fee', array_merge(['shop_id' => $this->shopId], $params));
    }

    public function createOrder(array $orderData): array
    {
        return $this->post('/v2/shipping-order/create', array_merge(['shop_id' => $this->shopId], $orderData));
    }

    private function cachedMasterData(string $key, callable $resolver): array
    {
        $cacheKey = 'ghn:master-data:' . $key;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $result = $resolver();
        if (($result['code'] ?? null) === 200 && !empty($result['data'])) {
            Cache::put($cacheKey, $result, now()->addDay());
        }

        return $result;
    }

    protected function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client()->get($uri, $query);
            return $response->json() ?? ['code' => 1, 'message' => 'Lỗi dữ liệu GHN'];
        } catch (\Exception $e) {
            Log::error('GHN GET Error: ' . $e->getMessage());
            return ['code' => -1, 'message' => 'Lỗi kết nối GHN'];
        }
    }

    protected function post(string $uri, array $payload): array
    {
        try {
            $response = $this->client()->post($uri, $payload);
            return $response->json() ?? ['code' => 1, 'message' => 'Lỗi dữ liệu GHN'];
        } catch (\Exception $e) {
            Log::error('GHN POST Error: ' . $e->getMessage());
            return ['code' => -1, 'message' => 'Lỗi kết nối GHN'];
        }
    }
}