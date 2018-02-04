<?php

namespace MongoDBBundle;
use MongoDBBundle\Exception\ConfigurationException;

/**
 * Class ConnectionUri
 */
class ConnectionUri
{
    const PREFIX = 'mongodb://';

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @param array $connection
     */
    public function __construct(array $connection)
    {
        $this->validate($connection);
        $this->host = $connection['host'];
        $this->port = $connection['port'];
        $this->username = $connection['user'] ?? null;
        $this->password = $connection['pass'] ?? null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $credentials = '';

        if (!empty($this->username) && !empty($this->password)) {
            $credentials = $this->username . ':' . $this->password . '@';
        }

        return self::PREFIX . $credentials . $this->host . ':' . $this->port;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    private function validate(array $connection)
    {
        if (empty($connection['host'])) {
            throw new ConfigurationException('Missing connection configuration: host');
        }
        if (empty($connection['port'])) {
            throw new ConfigurationException('Missing connection configuration: port');
        }
    }
}