<?php

namespace LaravelFCM\Response;

use Psr\Http\Message\ResponseInterface;
use LaravelFCM\Response\Exceptions\ServerResponseException;
use LaravelFCM\Response\Exceptions\InvalidRequestException;
use LaravelFCM\Response\Exceptions\UnauthorizedRequestException;

/**
 * Class BaseResponse.
 */
abstract class BaseResponse
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const ERROR = 'error';
    const MESSAGE_ID = 'name';

    /**
     * @var bool
     */
    protected $logEnabled = false;
    protected $responseInJson;

    /**
     * BaseResponse constructor.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->isJsonResponse($response);
        $this->logEnabled = app('config')->get('fcm.log_enabled', false);
        $responseBody = $response->getBody();
        $this->responseInJson = json_decode($responseBody, true);

        $this->parseResponse($this->responseInJson);
    }

    /**
     * Check if the response given by fcm is parsable.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws InvalidRequestException
     * @throws ServerResponseException
     * @throws UnauthorizedRequestException
     */
    private function isJsonResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 404 || $response->getStatusCode() == 400) {
            return;
        }

        // if ($response->getStatusCode() == 400) {
        //     throw new InvalidRequestException($response);
        // }

        if ($response->getStatusCode() == 401) {
            throw new UnauthorizedRequestException($response);
        }

        throw new ServerResponseException($response);
    }

    /**
     * parse the response.
     *
     * @param array $responseInJson
     */
    abstract protected function parseResponse($responseInJson);

    /**
     * Log the response.
     */
    abstract protected function logResponse();
}
