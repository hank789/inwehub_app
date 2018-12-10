<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 23/07/2018
 * Time: 11:20
 */

namespace App\Third\AliCdn;


interface CdnInterface
{
    // domain url
    const API_URL = 'https://cdn.aliyuncs.com';

    // action操作
    const ACTION_REFRESH_OBJECT_CACHES  = 'RefreshObjectCaches';
    const ACTION_DESCRIBE_REFRESH_TASKS = 'DescribeRefreshTasks';

    const TYPE_DIRECTORY = 1;
    const TYPE_FILE      = 2;

    /**
     * @param string $path
     * @param int $type
     */
    public function refreshCache(string $path, int $type = self::TYPE_DIRECTORY);

    /**
     * @param string $taskId
     *
     * @return array|string
     * @throws \Exception
     */
    public function getRefreshResult(string $taskId);
}