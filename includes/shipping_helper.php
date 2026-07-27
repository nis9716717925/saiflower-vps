<?php

require_once __DIR__ . '/shipping_config.php';

/**
 * Calculate driving distance and shipping fee from store to a delivery address.
 *
 * @return array{status: string, distance_km?: float, shipping_fee?: int, message?: string}
 */
function calculate_shipping_from_address(string $destination): array
{
    $destination = trim($destination);
    if ($destination === '') {
        return ['status' => 'error', 'message' => 'Delivery address is required.'];
    }

    $origin = STORE_LAT . ',' . STORE_LNG;
    $params = http_build_query([
        'origins'      => $origin,
        'destinations' => $destination,
        'mode'         => 'driving',
        'units'        => 'metric',
        'key'          => GOOGLE_MAPS_API_KEY,
    ]);

    $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . $params;

    $response = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    if ($response === false) {
        $context = stream_context_create(['http' => ['timeout' => 10]]);
        $response = @file_get_contents($url, false, $context);
    }

    if ($response === false) {
        return ['status' => 'error', 'message' => 'Unable to reach maps service. Please try again.'];
    }

    $data = json_decode($response, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'OK') {
        $apiMessage = $data['error_message'] ?? ($data['status'] ?? 'Unknown error');
        return ['status' => 'error', 'message' => 'Could not calculate distance: ' . $apiMessage];
    }

    $element = $data['rows'][0]['elements'][0] ?? null;
    if (!$element || ($element['status'] ?? '') !== 'OK') {
        return ['status' => 'error', 'message' => 'We could not find a route to this address. Please check and try again.'];
    }

    $meters = (int) ($element['distance']['value'] ?? 0);
    $distanceKm = round($meters / 1000, 2);
    $shippingFee = (int) round($distanceKm * SHIPPING_RATE_PER_KM);

    return [
        'status'       => 'ok',
        'distance_km'  => $distanceKm,
        'shipping_fee' => $shippingFee,
        'distance_text' => $element['distance']['text'] ?? ($distanceKm . ' km'),
    ];
}
