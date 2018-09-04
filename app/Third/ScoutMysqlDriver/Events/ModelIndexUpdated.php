<?php

namespace App\Third\ScoutMysqlDriver\Events;

class ModelIndexUpdated
{
    public $indexName;

    /**
     * Create a new event instance.
     *
     * @param $indexName
     */
    public function __construct($indexName)
    {
        $this->indexName = $indexName;
    }
}
