<?php

declare(strict_types=1);
require_once __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Header;
use GuzzleHttp\Psr7\Uri;

$username = '';
$password = '';

$url = 'https://organicalpha-international.myshopify.com/admin/api/2023-07/products.json?limit=250';
$client = new Client(['auth' => [$username, $password]]);

try {
    $response = $client->get($url);
    $products = json_decode($response->getBody()->getContents(), true);
    $links = $response ? Header::parse($response->getHeader('Link')) : [];
    foreach ($links as $link) {
        if ('next' === $link['rel']) {
            $nextPage = new Uri(rtrim(ltrim($link[0], '<'), '>'));
            $response = $client->get($nextPage);
            $products = array_merge($products, json_decode($response->getBody()->getContents(), true));
        }
    }

    echo 'Found '.count($products['products']).' products'.PHP_EOL;
    $arrayWithCombinedFields = [];
    foreach ($products['products'] as $product) {

        $productId = $product['id'];
        $metaUrl = 'https://organicalpha-international.myshopify.com/admin/api/2023-07/products/'.$productId.'/metafields.json';
        $response = $client->get($metaUrl);
        $metafields = json_decode($response->getBody()->getContents(), true);
        foreach ($metafields['metafields'] as $metafield) {
            $field = $metafield['namespace'].'@'.$metafield['key'].'@'.$metafield['type'];
            $arrayWithCombinedFields[] = $field;
        }
    }
    echo 'Found '.count($arrayWithCombinedFields).' metafields'.PHP_EOL;
    //keep only unique values
    $arrayWithCombinedFields = array_unique($arrayWithCombinedFields);
    echo 'Found '.count($arrayWithCombinedFields).' unique metafields'.PHP_EOL;
    $i = 0;

    foreach ($arrayWithCombinedFields as $metaField) {
        $db = new SQLite3('metafields.db');
        $db->exec('CREATE TABLE IF NOT EXISTS metafields (namespace TEXT)');
        $sql = sprintf("INSERT INTO metafields (namespace) VALUES ('%s')", $metaField);
        $db->exec($sql);
        //counter
        $i++;
        echo 'Inserted '.$i.' metafields'.PHP_EOL;
    }
    $graphqlClient = new Client([
        'base_uri' => 'https://organicalpha-international.myshopify.com/admin/api/2024-01/graphql.json',
        'auth' => [$username, $password],
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);
    $query = 'metafieldDefinitions(first: 250, ownerType: PRODUCT) {
    edges {
      node {
        name
      }
    }
  }';
    $content = json_encode([
        'query' => $query,
        'operationName' => 'Q2',
    ]);

    $content = '{"query":"query Q2 {\\n  metafieldDefinitions(first: 250, ownerType: PRODUCT) {\\n    edges {\\n      node {\\n        namespace\\n\\t\\t\\t},\\n\\t\\t\\t      node {\\n        name\\n\\t\\t\\t\\t\\t\\t},\\n\\t\\t\\t\\t\\t\\t      node {\\n        type{name}\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t},\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t      node {\\n        key\\n\\t\\t\\t\\t\\t\\t\\t\\t\\t},\\n    }\\n  }\\n}","operationName":"Q2"}';

    $response2 = $graphqlClient->request(
        'POST',
        '',
        ['body' => $content]
    );
    $metafields = json_decode($response2->getBody()->getContents(), true);
    $arrayWithCombinedFields1 = [];
    foreach ($metafields['data']['metafieldDefinitions']['edges'] as $metafield) {
        $field = $metafield['node']['namespace'].'@'.$metafield['node']['key'].'@'.$metafield['node']['type']['name'];
        $arrayWithCombinedFields1[] = $field;
    }
    echo 'Found '.count($arrayWithCombinedFields1).' metafields definitiion'.PHP_EOL;
    //keep only unique values
    $arrayWithCombinedFields = array_unique($arrayWithCombinedFields);
    echo 'Found '.count($arrayWithCombinedFields1).' unique metafields definitiion'.PHP_EOL;
    $i = 0;
    foreach ($arrayWithCombinedFields1 as $metaField) {
        $db = new SQLite3('metafields.db');
        $db->exec('CREATE TABLE IF NOT EXISTS metafields (namespace TEXT)');
        $sql = sprintf("INSERT INTO metafields (namespace) VALUES ('%s')", $metaField);
        $db->exec($sql);
        //counter
        $i++;
        if (0 === $i % 100) {
            echo 'Inserted '.$i.' metafields definitiion'.PHP_EOL;
        }

    }

} catch (GuzzleException $e) {
    echo $e->getMessage();
}
