<?php

declare(strict_types=1);

namespace App\DataCollector;

use App\Has\ApiLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

// Look this up
//use function Symfony\Component\String\u;


/**
 * Collects OAS API calls to feed the debug bar for example,
 * and perhaps an online log for admins later on, things like that.
 *
 * Class OasApiCollector
 * @package App\DataCollector
 */
final class OasApiCollector extends DataCollector
{
    use ApiLogger;

    /**
     * Collects data for the given Request and Response.
     * We collect the transactions to the external OAS API of MV (sick of abbr yet?)
     * that were made during the making of the response to the request.
     *
     * @param Request $request
     * @param Response $response
     * @param Throwable|null $exception
     */
    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        // TODO: this is not right -> only serializable data should go in here
        $this->data['transactions'] = $this->getApiLogger()->getTransactions();
    }

    // Is this called when the kernel is asked to process another request ?
    // When is it called ?
    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName(): string
    {
        return "oas_collector";
//        return self::class;
    }

    public function getTransactions(): array
    {
        return $this->data['transactions'] ?? [];
    }
}