<?php
/**
 * Created by PhpStorm.
 * User: birjemin
 * Date: 23/07/2018
 * Time: 11:13
 */
namespace App\Third\AliCdn;


use App\Third\AliCdn\Exception\CdnException;

/**
 * Class Cdn
 */
class Cdn implements CdnInterface
{
    /** @var string $result */
    private $result;

    /**
     * @param string $path
     * @param int $type
     *
     * @return array|string
     * @throws \Exception
     */
    public function refreshCache(string $path, int $type = self::TYPE_DIRECTORY)
    {
        return $this->execute([
            'Action'     => self::ACTION_REFRESH_OBJECT_CACHES,
            'ObjectPath' => $path,
            'ObjectType' => $type == self::TYPE_DIRECTORY ? 'Directory' : 'File'
        ]);
    }

    /**
     * @param string $taskId
     *
     * @return array|string
     */
    public function getRefreshResult(string $taskId)
    {
        return $this->execute([
            'Action' => self::ACTION_DESCRIBE_REFRESH_TASKS,
            'TaskId' => $taskId
        ]);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function execute(array $params)
    {
        try {
            $this->result = file_get_contents_curl($this->buildUrl($params),false);
            return $this->parseResult();
        } catch (\Exception $e) {
            throw new CdnException('execute error', (array)$e->getMessage());
        }
    }

    /**
     * @param array $param
     *
     * @return string
     */
    private function buildUrl(array $param)
    {
        $url =  self::API_URL . '?&' . http_build_query((new Sign($param))->buildParams());
        return $url;
    }

    /**
     * parse result
     * @return string
     */
    private function parseResult()
    {
        return $this->result;
    }
}