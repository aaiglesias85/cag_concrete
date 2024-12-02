<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/apigeeconnect/v1/tether.proto

namespace Google\Cloud\ApigeeConnect\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The proto definition of url.
 * A url represents a URL and the general form represented is:
 *  `[scheme://][google.cloud.apigeeconnect.v1.Url.host][path]`
 *
 * Generated from protobuf message <code>google.cloud.apigeeconnect.v1.Url</code>
 */
class Url extends \Google\Protobuf\Internal\Message
{
    /**
     * Scheme.
     *
     * Generated from protobuf field <code>.google.cloud.apigeeconnect.v1.Scheme scheme = 1;</code>
     */
    private $scheme = 0;
    /**
     * Host or Host:Port.
     *
     * Generated from protobuf field <code>string host = 2;</code>
     */
    private $host = '';
    /**
     * Path starts with `/`.
     *
     * Generated from protobuf field <code>string path = 3;</code>
     */
    private $path = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $scheme
     *           Scheme.
     *     @type string $host
     *           Host or Host:Port.
     *     @type string $path
     *           Path starts with `/`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Apigeeconnect\V1\Tether::initOnce();
        parent::__construct($data);
    }

    /**
     * Scheme.
     *
     * Generated from protobuf field <code>.google.cloud.apigeeconnect.v1.Scheme scheme = 1;</code>
     * @return int
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Scheme.
     *
     * Generated from protobuf field <code>.google.cloud.apigeeconnect.v1.Scheme scheme = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setScheme($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\ApigeeConnect\V1\Scheme::class);
        $this->scheme = $var;

        return $this;
    }

    /**
     * Host or Host:Port.
     *
     * Generated from protobuf field <code>string host = 2;</code>
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Host or Host:Port.
     *
     * Generated from protobuf field <code>string host = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setHost($var)
    {
        GPBUtil::checkString($var, True);
        $this->host = $var;

        return $this;
    }

    /**
     * Path starts with `/`.
     *
     * Generated from protobuf field <code>string path = 3;</code>
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Path starts with `/`.
     *
     * Generated from protobuf field <code>string path = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setPath($var)
    {
        GPBUtil::checkString($var, True);
        $this->path = $var;

        return $this;
    }

}

