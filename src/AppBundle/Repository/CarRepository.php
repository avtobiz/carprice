<?php

namespace AppBundle\Repository;

use MongoDB\Collection;
use MongoDBBundle\Client;
use \MongoDB\BSON\ObjectId;

/**
 * Class CarRepository
 */
class CarRepository
{
    const COLLECTION_NAME = 'auto';

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
     * @param array $params
     * @param int   $count
     *
     * @return ObjectId
     *
     */
    public function createJob($params, $count)
    {
        $res = $this->collection->insertOne([
            'created_at'    => (new \MongoDB\BSON\UTCDateTime(time() * 1000)),
            'params'        => $params,
            'count'         => $count,
            'tasks'         => [],
        ]);

        return $res->getInsertedId();
    }

    /**
     * @param ObjectId  $jobId
     * @param array     $tasks
     *
     * @return \MongoDB\UpdateResult
     */
    public function addTasksForJob(ObjectId $jobId, array $tasks)
    {

        return $this->collection->updateOne(
            ['_id'   => $jobId],
            ['$addToSet' => ['tasks' => ['$each' => $tasks]]]
        );
    }

    /**
     * @param ObjectId $id
     *
     * @return array
     */
    public function findById(ObjectId $id)
    {
        return $this->collection->findOne(['_id'=>$id]);
    }
}
