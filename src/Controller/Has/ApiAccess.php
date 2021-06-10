<?php


namespace App\Controller\Has;


use App\Adapter\ApiExceptionAdapter;
use App\Factory\ApiFactory;
use MjOpenApi\ApiException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


trait ApiAccess
{
    use UserSession;
    use FlashBag;
    use Translator;

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
     * NO.
     * We need a way to get multiple Services, this can't be in this Trait.
     * Perhaps it's okay to load the services we need, on second thought.
     *
     * @param ApiException $exception
     * @return Response
     */
    public function renderApiException(ApiException $exception, Request $request) : Response
    {
        $data = $this->getApiExceptionData($exception);

        if (isset($data['code']) && (Response::HTTP_UNAUTHORIZED == $data['code'])) {
            $this->userSession->logout();
            $this->getFlashBag()->add('warning', $this->trans("flash.error.requires_authentication"));
            // todo: redirect to /gate.html
            return new RedirectResponse("/login.html?redirect=".urlencode($request->getRequestUri()));
        }

        $apiResponse = $this->getApiExceptionAdapter()->toHtml($exception);

        // todo: fetch the env, react accordingly

        return $this->render('error/api_exception.html.twig', [
            'apiResponse' => $apiResponse,
        ]);
    }
}