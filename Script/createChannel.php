<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Script;

$token = '';
use SQLite3;

require_once __DIR__ . '/../vendor/autoload.php';

$url = 'https://channel-management.productsup.com/V1/export';

$payload = [
    'name' => 'Shopify Metafields - Karo Pharma Shop',
    'encoding' => '',
    'pseudo' => false,
    'allowXml' => true,
    'validDelimiter' => 'TAB,COMMA,PIPE',
    'roiOptions' => 'PAUSE',
    'logoUrl' => '',
    'createDelta' => true,
    'deltaKeyUnique' => true,
    'deltaNewFilename' => 'newFile.csv',
    'deltaChangedFilename' => 'changedFile.csv',
    'deltaDeletedFilename' => 'deletedFile.csv',
    'deltaUnchangedFilename' => 'unchangedFile.csv',
    'deltaSkippedFilename' => 'skippedFile.csv',
    'deltaSingleFile' => false,
    'customClassName' => 'Blank',
    'defaultFilename' => 'feed.tsv',
];

$client = new \GuzzleHttp\Client();

// UNCOMMENT THIS TO CREATE CHANNEL AND SAVE CHANNEL ID


//$response = $client->request('POST', $url, [
//    'headers' => [
//        'Authorization' => 'Bearer '.$token,
//        'Content-Type' => 'application/json',
//    ],
//    'json' => $payload,
//]);
//$responseData = json_decode($response->getBody()->getContents(), true);
//var_dump($responseData);
//die;
//CHANNEL ID = "id" => 36696
$channelId = 36696;



//read sqlite db metafieldds.db
$db = new SQLite3('metafields.db');
$results = $db->query('SELECT * FROM metafields');

$payload = [
    'entries' => [
        [
            'requestKey' => '1',
            'exportField' => [
                'fieldName' => 'ownerId',
                'alias' => 'Product ID',
                'mandatory' => true,
                'flag' => 'colors',
                'isDefaultTrackingColumn' => true,
                'isUniqueColumn' => true,
                'order' => 1,
                'deltaIgnore' => true,
                'deltaKey' => true,
                'automapIgnore' => true,
                'description' => 'Product ID',
                'format' => 'String (Unicode Characters)',
            ],
        ],
    ],
];
$id = 2;
$array = [];
while ($row = $results->fetchArray()) {
    $payload['entries'][] = [
        'requestKey' => (string)$id,
        'exportField' => [
            'fieldName' => $row['namespace'],
            'mandatory' => false,
            'order' => (string)$id,
        ],
    ];
    //save from namespace last part after @ ex namespace@key@type
    preg_match('/([^@]+)$/', $row['namespace'], $matches);
    //var_dump($matches[0]);
    $array[$id] = $matches[0];
    $id++;
}

$url = 'https://channel-management.productsup.com/V1/export/'.$channelId.'/export-fields/bulk-create';

try {
    $response = $client->request('POST', $url, [
        'headers' => [
            'Authorization' => 'Bearer '.$token,

            'Content-Type' => 'application/json',
        ],
        'json' => $payload,
    ]);
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    var_dump($e->getMessage());
    var_dump($e->getCode());
    var_dump($e->getResponse()->getBody()->getContents());
    die();
}

$responseData = json_decode($response->getBody()->getContents(), true);
echo($responseData);
