<?php

namespace AppBundle\Serializer\Normalizer\BSON;

use \MongoDB\BSON\ObjectId;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ObjectIdNormalizer
 */
class ObjectIdNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof ObjectId;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return (string)$object;
    }
}
