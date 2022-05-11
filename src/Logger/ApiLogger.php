<?php

declare(strict_types=1);

namespace App\Logger;

use App\Logger\Model\ApiTransaction;
use Psr\Http\Message\ResponseInterface;


/**
 * Helper Service to log communication with MV OpenAPI OASv3.
 * This is triggered by custom middleware in the API HTTP Client, see ApiFactory.
 *
 * Class ApiLogger
 * @package App\Logger
 */
final class ApiLogger
{

    /** @var ApiTransaction[] $transactions */
    protected $transactions = array();

    /**
     * @return ApiTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @param ApiTransaction $tx
     * @return $this
     */
    public function logTransaction(ApiTransaction $tx): self
    {
        $this->transactions[] = $tx;

        return $this;
    }

    /**
     * Middleware that logs transactions.  (to show them in the debug bar, for example)
     * This is meant to be used as middleware in Guzzle Http Clients.
     * See ApiFactory
     *
     * @return callable Returns a function that accepts the next handler.
     */
    public function cookClientMiddleware()
    {
        return function (callable $handler) {
            return function ($request, array $options) use ($handler) {
                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request) {
                        // $this is propagated down here, but may not be someday (vendor)

                        $tx = new ApiTransaction(
                            sprintf("%d", count($this->getTransactions())),
                            $request, $response
                        );
                        $this->logTransaction($tx);

                        return $response;
                    }
                );
            };
        };
    }
}