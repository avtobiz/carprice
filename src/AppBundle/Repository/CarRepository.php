<?php

namespace AppBundle\Repository;

use MongoDB\Collection;
use MongoDBBundle\Client;
use \MongoDB\BSON\ObjectId;
use Symfony\Component\ExpressionLanguage\Tests\Node\Obj;

/**
 * Class CarRepository
 */
class CarRepository
{
    const COLLECTION_NAME = 'cars';

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

    /**
     * @param ObjectId $id
     *
     * @return \MongoDB\Driver\Cursor
     */
    public function findAllByJobId(ObjectId $id)
    {
        return $this->collection->find(['job' => $id]);
    }

    /**
     * @param ObjectId $id
     *
     * @return int
     */
    public function countByJobId(ObjectId $id)
    {
        return $this->collection->count(['job' => $id]);
    }

    /**
     *
     * @param string $autoId
     * @return array
     */
    public function findByAutoId(string $autoId)
    {
        return $this->collection->findOne(['autoData.autoId' => $autoId]);
    }

    /**
     * @param ObjectId $id
     * @param ObjectId $job
     * @param string $hash
     *
     * @return \MongoDB\UpdateResult
     */
    public function setHash(ObjectId $id, string $hash)
    {
        return $this->collection->updateOne(
            ['_id' => $id],
            ['$set' => ['_hash' => $hash]],
            ['upsert' => true]
        );
    }

    /**
     *
     * @param string $autoId
     * @param ObjectId $jobId
     *
     * @return array
     */
    public function findByAutoIdAndJobId(string $autoId, ObjectId $jobId)
    {
        return $this->collection->findOne(['autoData.autoId' => $autoId, 'job' => $jobId]);
    }

    /**
     *
     * @param string $phone
     * @param ObjectId $jobId
     *
     * @return \MongoDB\Driver\Cursor
     */
    public function findByPhoneJob(string $phone, ObjectId $jobId)
    {
        return $this->collection->find(['userPhoneData.phoneId' => $phone, 'job' => $jobId]);
    }

    /**
     *
     * @param int $phone
     * @param ObjectId $jobId
     *
     * @return int
     */
    public function countByPhoneJob(int $phone, ObjectId $jobId)
    {
        return $this->collection->count(['userId' => $phone, 'job' => $jobId]);
    }
}
