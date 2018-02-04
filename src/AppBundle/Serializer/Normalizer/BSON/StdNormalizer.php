<?php

namespace AppBundle\Serializer\Normalizer\BSON;

use \MongoDB\BSON\ObjectId;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class StdNormalizer
 */
class StdNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof stdClass;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        foreach ($object as $property => $value) {
            if (is_object($value)) {
                $data[$property] = $this->serializer->normalize($value, $format, $context);
            } elseif (is_array($value)) {
                foreach ($value as $p=>$v){
                    $subData = [];
                    $subData[$p] = $this->serializer->normalize($v, $format, $context);
                    $data[$property][] =$subData;
                }
            } else {
                $data[$property] = $value;
            }
        }

        return $data;
    }
}
