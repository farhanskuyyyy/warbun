<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;

class DeliveryPoint
{
    public static function rules(string $prefix = 'shipping_'): array
    {
        return [
            $prefix.'latitude' => ['nullable', 'required_with:'.$prefix.'longitude', 'numeric', 'between:-90,90'],
            $prefix.'longitude' => ['nullable', 'required_with:'.$prefix.'latitude', 'numeric', 'between:-180,180'],
        ];
    }

    public static function snapshot(array $data, bool $delivery): array
    {
        $point = $delivery ? Validator::make($data, self::rules())->validate() : [];

        return [
            'shipping_latitude' => $point['shipping_latitude'] ?? null,
            'shipping_longitude' => $point['shipping_longitude'] ?? null,
        ];
    }

    public static function url(mixed $latitude, mixed $longitude): ?string
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude) || ! is_finite((float) $latitude) || ! is_finite((float) $longitude) || abs((float) $latitude) > 90 || abs((float) $longitude) > 180) {
            return null;
        }

        $latitude = number_format((float) $latitude, 7, '.', '');
        $longitude = number_format((float) $longitude, 7, '.', '');

        return 'https://www.openstreetmap.org/?'.http_build_query(['mlat' => $latitude, 'mlon' => $longitude]).'#map=18/'.$latitude.'/'.$longitude;
    }
}
