<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use OpenSearch\ClientBuilder;

function putLine(): void
{
    echo str_repeat('-', 40) . "\n";
}

try {
    // 簡単な接続（localhost:9200）
    $client = ClientBuilder::create()
        ->setHosts(['http://localhost:9200'])
        ->build();
    // 接続確認
    $info = $client->info();
    echo "Connected to OpenSearch:\n" . print_r($info, true) . "\n\n";
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
    exit(1);
}

$result = shell_exec('curl -X DELETE "http://localhost:9200/test_index"');

echo print_r(json_decode($result, true), true) . "\n\n";
putLine();

try {
    // インデックス作成
    $client->indices()->create([
        'index' => 'test_index',
        'body' => [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0
            ],
        ],
    ]);
    echo "Index 'test_index' created successfully.\n\n";
    putLine();
} catch (Exception $e) {
    echo "Index creation error: " . print_r(json_decode($e->getMessage(), true), true) . "\n";
    exit(1);
}

try {
    // ドキュメントを投入
    $response = $client->index([
        'index' => 'test_index',
        'body' => [
            'message' => 'Hello OpenSearch from PHP'
        ]
    ]);
    echo "index response:\n" . print_r($response, true) . "\n\n";
    putLine();
} catch (Exception $e) {
    echo "Indexing error: " . print_r(json_decode($e->getMessage(), true), true) . "\n";
}

try {
    // 検索
    $search = $client->search([
        'index' => 'test_index',
        'body' => [
            'query' => [
                'match_all' => (object)[]
            ]
        ]
    ]);

    echo "search response:\n" . print_r($search, true) . "\n";
    putLine();

} catch (Exception $e) {
    echo "Search error: " . $e->getMessage() . "\n";
}
