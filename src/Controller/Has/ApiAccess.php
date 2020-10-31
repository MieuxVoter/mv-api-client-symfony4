<?php


namespace App\Controller\Has;


use App\Adapter\ApiExceptionAdapter;
use App\Factory\ApiFactory;
use MjOpenApi\ApiException;
use Symfony\Component\HttpFoundation\Response;


trait ApiAccess
{
    /** @var ApiFactory $api_factory */
    protected $api_factory;

    /** @var ApiExceptionAdapter $api_exception_adapter */
    protected $api_exception_adapter;

    ///
    ///

    /**
     * @return ApiFactory
     */
    public function getApiFactory() : ApiFactory
    {
        return $this->api_factory;
    }

    /**
     * @required
     * @param ApiFactory $api_factory
     */
    public function setApiFactory(ApiFactory $api_factory): void
    {
        $this->api_factory = $api_factory;
    }

    /**
     * @return ApiExceptionAdapter
     */
    public function getApiExceptionAdapter(): ApiExceptionAdapter
    {
        return $this->api_exception_adapter;
    }

    /**
     * @required
     * @param ApiExceptionAdapter $api_exception_adapter
     */
    public function setApiExceptionAdapter(ApiExceptionAdapter $api_exception_adapter): void
    {
        $this->api_exception_adapter = $api_exception_adapter;
    }

    public function getApiExceptionData(ApiException $exception)
    {
        return $this->getApiExceptionAdapter()->toData($exception);
    }

    /**
     * Sugar to handle api exceptions in controllers.
     *
     * @param ApiException $exception
     * @return Response
     */
    public function renderApiException(ApiException $exception) : Response
    {
        $apiResponse = $this->getApiExceptionAdapter()->toHtml($exception);

        // todo: fetch the env, react accordingly

        return $this->render('error/api_exception.html.twig', [
            'apiResponse' => $apiResponse,
        ]);
    }
}