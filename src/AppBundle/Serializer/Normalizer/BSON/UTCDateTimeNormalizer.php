<?php

namespace AppBundle\Serializer\Normalizer\BSON;

use MongoDB\BSON\UTCDateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class UTCDateTimeNormalizer
 */
class UTCDateTimeNormalizer implements NormalizerInterface
{
    /**
     * Date format
     *
     * @var string
     */
    protected $dateFormat = \DateTime::ISO8601;

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof UTCDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->toDateTime()->format($this->dateFormat);
    }
}
