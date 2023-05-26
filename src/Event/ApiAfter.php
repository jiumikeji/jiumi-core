<?php
/**
 * @Date：2023/5/22 15:45
 * @Author LBG
 * @Link   http://www.hebei9.cn
 * @Copyright：Copyright (c) 2022 - 2035, 河北九米电子科技有限公司, Inc.
 */


namespace Jiumi\Event;

use Psr\Http\Message\ResponseInterface;

class ApiAfter
{
    protected ?array $apiData;

    protected ResponseInterface $result;

    public function __construct(?array $apiData, ResponseInterface $result)
    {
        $this->apiData = $apiData;
        $this->result = $result;
    }

    public function getApiData(): ?array
    {
        return $this->apiData;
    }

    public function getResult(): ResponseInterface
    {
        return $this->result;
    }
}