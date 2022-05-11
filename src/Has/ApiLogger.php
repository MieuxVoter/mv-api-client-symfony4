<?php

declare(strict_types=1);

namespace App\Has;


trait ApiLogger
{

    /** @var \App\Logger\ApiLogger */
    protected $api_logger;

    /**
     * @return \App\Logger\ApiLogger
     */
    public function getApiLogger(): \App\Logger\ApiLogger
    {
        return $this->api_logger;
    }

    /**
     * @required → DIC will call this <3
     * @param \App\Logger\ApiLogger $api_logger
     */
    public function setApiLogger(\App\Logger\ApiLogger $api_logger): void
    {
        $this->api_logger = $api_logger;
    }

}