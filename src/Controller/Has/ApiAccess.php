<?php


namespace App\Controller\Has;


use App\Factory\ApiFactory;


trait ApiAccess
{
    /** @var ApiFactory $api_factory */
    protected $api_factory;

    /**
     * @required
     * @param ApiFactory $api_factory
     */
    public function setApiFactory(ApiFactory $api_factory): void
    {
        $this->api_factory = $api_factory;
    }

    /**
     * @return ApiFactory
     */
    public function getApiFactory() : ApiFactory
    {
        return $this->api_factory;
    }
}