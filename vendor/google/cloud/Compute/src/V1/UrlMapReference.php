<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.UrlMapReference</code>
 */
class UrlMapReference extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>optional string url_map = 367020684;</code>
     */
    private $url_map = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $url_map
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>optional string url_map = 367020684;</code>
     * @return string
     */
    public function getUrlMap()
    {
        return isset($this->url_map) ? $this->url_map : '';
    }

    public function hasUrlMap()
    {
        return isset($this->url_map);
    }

    public function clearUrlMap()
    {
        unset($this->url_map);
    }

    /**
     * Generated from protobuf field <code>optional string url_map = 367020684;</code>
     * @param string $var
     * @return $this
     */
    public function setUrlMap($var)
    {
        GPBUtil::checkString($var, True);
        $this->url_map = $var;

        return $this;
    }

}

