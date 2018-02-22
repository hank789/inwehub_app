<?php namespace App\Console\Commands;
/**
 * @author: wanghui
 * @date: 2018/2/22 下午4:44
 * @email: wanghui@yonglibao.com
 */

use GuzzleHttp\Client;
use Illuminate\Console\Command;
class InitEs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:init';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init es to create index';
    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $this->createTemplate($client);
        $this->createIndex($client);
    }
    protected function createIndex(Client $client)
    {
        $url = config('scout.elasticsearch.hosts')[0] .'/'. config('scout.elasticsearch.index');
        $client->put($url, [
            'json' => [
                'settings' => [
                    'refresh_interval' => '5s',
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ],
                'mappings' => [
                    '_default_' => [
                        '_all' => [
                            'enabled' => false
                        ]
                    ]
                ]
            ]
        ]);
    }
    protected function createTemplate(Client $client)
    {
        $url = config('scout.elasticsearch.hosts')[0] . '/_template/rtf';
        $client->put($url, [
            'json' => [
                'template' => '*',
                'settings' => [
                    'number_of_shards' => 1
                ],
                'mappings' => [
                    '_default_' => [
                        '_all' => [
                            'enabled' => true
                        ],
                        'dynamic_templates' => [
                            [
                                'strings' => [
                                    'match_mapping_type' => 'string',
                                    'mapping' => [
                                        'type' => 'text',
                                        'analyzer' => 'ik_smart',
                                        'ignore_above' => 256,
                                        'fields' => [
                                            'keyword' => [
                                                'type' => 'keyword'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}