<?php

return [
    'tile_url' => env('MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'attribution' => env('MAP_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'),
    'latitude' => (float) env('MAP_DEFAULT_LATITUDE', -2.5),
    'longitude' => (float) env('MAP_DEFAULT_LONGITUDE', 118),
    'zoom' => (int) env('MAP_DEFAULT_ZOOM', 5),
];
