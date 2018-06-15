<?php

namespace AppBundle\Repository;

use MongoDB\Collection;
use MongoDBBundle\Client;
use \MongoDB\BSON\ObjectId;

/**
 * Class JobRepository
 */
class JobRepository
{
    const COLLECTION_NAME = 'jobs';

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
     *
     */
    public function createJob($params, $count)
    {
        $res = $this->collection->insertOne([
            'created_at'    => (new \MongoDB\BSON\UTCDateTime(time() * 1000)),
            'params'        => $params,
            'count'         => $count,
            'status'        => 'created',
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
        $data = array_map(function ($n) {
            return (int)$n;
        }, $tasks);

        return $this->collection->updateOne(
            ['_id'   => $jobId],
            ['$addToSet' => ['tasks' => ['$each' => $data]]]
        );
    }

    /**
     * @param ObjectId $jobId
     * @param array $tasks
     *
     * @return \MongoDB\UpdateResult
     */
    public function setCompletedTasksForJob(ObjectId $jobId, array $tasks)
    {
        return $this->collection->updateMany(
            ['_id' => $jobId],
            ['$pullAll' => ['tasks' => $tasks]]
        );
    }

    /**
     * @param ObjectId  $jobId
     * @param string    $status
     *
     * @return \MongoDB\UpdateResult
     */
    public function updateStatus(ObjectId $jobId, string $status)
    {
        return $this->collection->updateOne(
            ['_id' => $jobId],
            ['$set' => ['status' => $status]]
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

    /**
     * @return array
     */
    public function getJobForExecute()
    {
        return $this->collection->findOne(['status' => 'created']);
    }

    /**
     * @param string $status
     *
     * @return array
     */
    public function findOneByStatus(string $status)
    {
        return $this->collection->findOne(['status' => $status]);
    }
}
