<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use OpenSearch\ClientBuilder;

const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'; // phpcs:ignore

function putLine(): void
{
    echo str_repeat('-', 40) . "\n";
}

function getPage(string $name): array
{
    // Wikipediaのページを取得
    $options = ['headers' => ['User-Agent' => USER_AGENT]];
    $client = new GuzzleHttp\Client();
    try {
        $result = $client->get(
            'https://ja.wikipedia.org/w/rest.php/v1/page/' . $name,
            $options
        );

        return json_decode($result->getBody()->getContents(), true);
    } catch (Exception $e) {
        echo "HTTP error: " . $e->getMessage() . "\n";
        exit(1);
    }
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

$result = shell_exec('curl -X DELETE "http://localhost:9200/kuromoji_index"');

echo print_r(json_decode($result, true), true) . "\n\n";
putLine();

try {
    // インデックス作成
    $client->indices()->create([
        'index' => 'kuromoji_index',
        'body' => [
            'settings' => [
                'analysis' => [
                    'filter' => [
                        'ja_pos_filter' => [
                            'type' => 'kuromoji_part_of_speech',
                            'stoptags' => ['記号,一般', '記号,空白'],
                        ],
                        'ja_baseform' => [
                            'type' => 'kuromoji_baseform',
                        ],
                        'ja_stemmer' => [
                            'type' => 'kuromoji_stemmer',
                            'minimum_length' => 2,
                        ],
//                        'ignore_words_stop' => [
//                            'type' => 'stop',
//                            'stopwords' => $this->ignoreWords,
//                        ],
//                        'min_length_filter' => [
//                            'type' => 'length',
//                            'min' => $this->wordLength,
//                        ],
                    ],
                    'analyzer' => [
                        'ja_analyzer' => [
                            'type' => 'custom',
                            'tokenizer' => 'kuromoji_tokenizer',
                            'filter' => [
                                'lowercase',
                                'ja_baseform',
                                'ja_pos_filter',
                                'ja_stemmer',
//                                'ignore_words_stop',
//                                'min_length_filter',
                            ],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'title' => [
                        'type' => 'keyword',
                    ],
                    'source' => [
                        'type' => 'text',
                        'analyzer' => 'ja_analyzer',
                        'fielddata' => true,
                    ],
                ],
            ],
        ],
    ]);
    echo "Index 'kuromoji_index' created successfully.\n\n";
    putLine();
} catch (Exception $e) {
    echo "Index creation error: " . print_r(json_decode($e->getMessage(), true), true) . "\n";
    exit(1);
}

try {
    // ドキュメントを投入
    $people = [
        '上本崇司',
        '中村健人',
        '中村奨成',
        '中村貴浩',
        '中﨑翔太',
        '久保修',
        '二俣翔一',
        '仲田侑仁',
        '佐々木泰',
        '佐藤啓介',
        '佐藤柳之介',
        '内田湘大',
        '内間拓馬',
        '坂倉将吾',
        '堂林翔太',
    ];
    foreach ($people as $person) {
        $result = getPage($person);
        echo print_r(array_keys($result), true) . "\n\n";
        $response = $client->index([
            'index' => 'kuromoji_index',
            'body' => [
                'title' => $result['title'],
                'source' => $result['source'],
            ]
        ]);
        echo "index response:\n" . print_r($response, true) . "\n\n";
        putLine();
    }
} catch (Exception $e) {
    echo "Indexing error: " . print_r(json_decode($e->getMessage(), true), true) . "\n";
}

try {
    // 検索
    $search = $client->search([
        'index' => 'kuromoji_index',
        'body' => [
            'query' => [
                'match_all' => (object)[]
            ]
        ]
    ]);

    echo "search response:\n";
    echo "\t" . "took: " . $search['took'] . "\n";
    echo "\t" . "_shards:" .print_r($search['_shards'], true) . "\n";
    echo "\t" . "hits[total]:" . print_r($search['hits']['total'], true) . "\n";
    echo "\t" . "hits[max_score]:" . print_r($search['hits']['max_score'], true) . "\n";
    putLine();
    file_put_contents('result-kuromoji_search.txt', print_r($search, true));

} catch (Exception $e) {
    echo "Search error: " . $e->getMessage() . "\n";
}
