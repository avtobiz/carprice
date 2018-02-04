<?php

namespace MongoDBBundle;

use MongoDBBundle\Exception\ConfigurationException;
use MongoDBBundle\Exception\EnvironmentException;
use MongoDB\Client as MongoDBClient;

/**
 * Class Client
 */
class Client extends MongoDBClient
{
    /**
     * @var ConnectionUri
     */
    private $connectionUri;

    /**
     * @var ConnectionOptions
     */
    private $connectionOptions;

    /**
     * @var string
     */
    private $defaultDatabaseName;

    /**
     * @param array $params
     *
     * @internal param \MongoDB\Driver\Manager $nativeClient
     */
    public function __construct(array $params)
    {
        $this->validateEnvironment();
        $this->retrieveParams($params);
        $this->defaultDatabaseName = $params['default_database']??'default';

        parent::__construct((string)$this->connectionUri);
    }

    /**
     * @return \MongoDB\Database
     */
    public function getDefaultDatabase()
    {
        return $this->selectDatabase($this->defaultDatabaseName);
    }

    private function validateEnvironment()
    {
        if (!class_exists('\MongoDB\Driver\Manager')) {
            throw new EnvironmentException('Missing dependencies: missing mongodb driver');
        }
    }

    private function retrieveParams(array $params)
    {
        if (!array_key_exists('connection', $params)) {
            throw new ConfigurationException('Missing configuration: connection');
        }

        $this->connectionUri = new ConnectionUri($params['connection']);
        $this->connectionOptions = new ConnectionOptions($params['options']);
    }
}