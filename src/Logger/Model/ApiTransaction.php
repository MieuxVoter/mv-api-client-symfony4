<?php

declare(strict_types=1);

namespace App\Logger\Model;


use DateTime;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


/**
 * Light wrapper for HTTP transactions made with the OAS API.
 *
 * Class ApiTransaction
 * @package App\Logger\Model
 */
class ApiTransaction
{
    /** @var string $identifier of the transaction.  Must be unique amongst transactions of the same kernel run. */
    protected $identifier;

    /** @var DateTime $made_at */
    protected $made_at;

    /** @var RequestInterface */
    protected $request;

    /** @var ResponseInterface */
    protected $response;

    // I can't do anything with response.body in the template, it's empty somehow.
    // But, if we collect it here, we can read it without problems.
    // If you figure out how to read collector.response.body in a twig template we can remove this.
    /** @var string $responseBody */
    public $responseBody;

    public $responseBodyPrettyJson;
    /**
     * ApiTransaction constructor.
     *
     * @param string $identifier
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws Exception
     */
    public function __construct(string $identifier, RequestInterface $request, ResponseInterface $response)
    {
        $this->identifier = $identifier;
        $this->made_at = new DateTime();
        $this->request = $request;
        $this->response = $response;

        // See $this->responseBody
        $bodyStream = $this->response->getBody();
        $this->responseBody = (string) $bodyStream;
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        } else {
            trigger_error("Response body stream can't be rewound.   Ouch.", E_USER_WARNING);
        }

        $this->responseBodyPrettyJson = json_format($this->responseBody);
    }

    public function isSuccess(): bool
    {
        return $this->getResponse()->getStatusCode() < 400;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return DateTime
     */
    public function getMadeAt(): DateTime
    {
        return $this->made_at;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

}
