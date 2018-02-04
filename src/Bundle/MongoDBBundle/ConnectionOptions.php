<?php

namespace MongoDBBundle;

/**
 * Class ConnectionOptions
 */
class ConnectionOptions
{
    const DEFAULT_OPTIONS = ["connect" => true];

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return empty($this->options) ? self::DEFAULT_OPTIONS : $this->options;
    }
}
