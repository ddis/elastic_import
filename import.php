<?php
/**
 * Created by PhpStorm.
 * User: ddis
 * Date: 08.03.19
 * Time: 19:17
 */

use Curl\Curl;

require_once 'vendor/autoload.php';

define("URL", "ms-indexer.local");
define("LIMIT", 5);

$startParams = [
    'products'  => '[{"visible":{"e":"1"}}]',
    'documents' => '[{"visible":{"e":"1"}}]',
    //'pages'     => '[{"visible":{"e":"1"}}]',
];

$startParams['limit'] = LIMIT;

$url = URL . '/v1/elasticsearch/import?';

$curl = new Curl();

$request = $curl->get($url . http_build_query($startParams));

if ($request->http_status_code == 200) {
    $response = json_decode($request->response, true);

    foreach ($response['data'] as $datum) {
        if ($datum['type'] == 'all') {
            continue;
        }

        $iterCount = ceil($datum['attributes']['total'] / LIMIT);

        for ($i = 1; $i <= $iterCount; $i++) {
            $params = [
                'limit'        => LIMIT,
                'offset'       => LIMIT * $i,
                $datum['type'] => $startParams[$datum['type']],
                'hash'         => $datum['attributes']['hash'],
            ];

            $request = $curl->get($url . http_build_query($params));
            $count   = round((LIMIT * $i) / $datum['attributes']['total'] * 100);

            print "{$datum['type']}: {$count}%\r";
        }
    }
}
