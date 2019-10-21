<?php

namespace AppBundle\Service\API;

use AppBundle\Repository\TokenKeeperRepository;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class RiaClient
 *
 * API documentations https://github.com/ria-com/auto-ria-rest-api/tree/master/AUTO_RIA_API
 */
class RiaClient extends GuzzleClient
{
    const API_KEY = '﻿1aqv1bExQJ9lUWN3pWw1jPQxB9L83j7PfLJ8ITsV';

    /**
     * @var TokenKeeperRepository
     */
    private $tokenKeeper;

    /**
     * @var string
     */
    private $token;

    /**
     * RiaClient constructor.
     *
     * @param TokenKeeperRepository $tokenKeeper
     */
    public function __construct($tokenKeeper)
    {
        $options = ['base_uri' => 'https://developers.ria.com'];
        parent::__construct($options);
        $this->tokenKeeper = $tokenKeeper;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $t = $this->tokenKeeper->getToken();

        return $this->token = $t['token'];
    }

    /**
     * @param string $token
     */
    public function refreshToken($token)
    {
        $this->tokenKeeper->updateStatus($token, 'over_limit');
    }

    /**
     * @param array $params
     *
     * @return null|ResponseInterface
     */
    public function searchAuto(array $params)
    {
        if (!isset($params['api_key'])) {
            $params['api_key'] = $this->getToken();
        }

        $uri = '/auto/search';
        $options['query'] = $params;

        return $this->doRequest(Request::METHOD_GET, $uri, $options);
    }

    /**
     * @param string|int $id
     * @param array $params
     *
     * @return null|ResponseInterface
     * @throws \Exception
     */
    public function infoAutoById($id, array $params = [])
    {
        if (!isset($params['api_key'])) {
            $params['api_key'] = $this->getToken();
        }

        $params['auto_id'] = $id;

        $uri = '/auto/info';
        $options['query'] = $params;

        return $this->doRequest(Request::METHOD_GET, $uri, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|null $options
     *
     * @throws \Exception
     *
     * @return null|ResponseInterface
     */
    private function doRequest($method, $uri, $options)
    {
        try {
            /** @var ResponseInterface $response */
            $response = $this->request($method, $uri, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        if (in_array($response->getStatusCode(), [429])) {
            $this->refreshToken($this->token);
        }

        return $response;
    }
}
