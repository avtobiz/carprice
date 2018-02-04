<?php

namespace AppBundle\Service\API;

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
//    const API_KEY = 'hblEVdm9aasEsWL54Mcj5wzD1bCnPJiOKHa7h23C';
    const API_KEY = 'NwVgGRITaTJnWQlnX3aJdOd85k01BiLlfODdXDcS';

    /**
     * RiaClient constructor.
     */
    public function __construct()
    {
        $options = ['base_uri' => 'https://developers.ria.com'];
        parent::__construct($options);
    }

    /**
     * @param array $params
     *
     * @return null|ResponseInterface
     */
    public function searchAuto(array $params)
    {
        $params['api_key'] = self::API_KEY;

        $uri = '/auto/search';
        $options['query'] = $params;

        return $this->doRequest(Request::METHOD_GET, $uri, $options);
    }

    /**
     * @param string|int $id
     *
     * @return null|ResponseInterface
     */
    public function infoAutoById($id)
    {
        $params['api_key'] = self::API_KEY;
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
            $response = $this->request($method, $uri, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }

    //vritual client functional
}