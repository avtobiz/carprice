<?php

namespace AppBundle\Repository;

use MongoDB\Collection;
use MongoDBBundle\Client;
use \MongoDB\BSON\ObjectId;

/**
 * Class TokenKeeper
 */
class TokenKeeperRepository
{
    const COLLECTION_NAME = 'ria_tokens';

    const LIMIT_NUMBER_OF_CALLS = 2900;

    /**
     * @var \MongoDBBundle\Client
     */
    private $client;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * JobRepository constructor.
     *
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->collection = $client->getDefaultDatabase()->selectCollection(self::COLLECTION_NAME);
    }

    /**
     * @param string $token
     * @param string $status
     *
     * @return \MongoDB\UpdateResult
     */
    public function updateStatus(string $token, string $status)
    {
        return $this->collection->updateOne(
            ['token' => $token],
            ['$set' => ['status' => $status]]
        );
    }

    /**
     * @return array
     */
    public function getToken()
    {
        return $this->collection->findOneAndUpdate(
            [
                'status' => 'active',
                'noc' => ['$lte' => self::LIMIT_NUMBER_OF_CALLS]
            ],
            [
                '$inc' => ['noc' => 1]
            ]);
    }
}
