<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/redis/v1beta1/cloud_redis.proto

namespace Google\Cloud\Redis\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The Cloud Storage location for the output content
 *
 * Generated from protobuf message <code>google.cloud.redis.v1beta1.GcsDestination</code>
 */
class GcsDestination extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Data destination URI (e.g.
     * 'gs://my_bucket/my_object'). Existing files will be overwritten.
     *
     * Generated from protobuf field <code>string uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $uri = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $uri
     *           Required. Data destination URI (e.g.
     *           'gs://my_bucket/my_object'). Existing files will be overwritten.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Redis\V1Beta1\CloudRedis::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Data destination URI (e.g.
     * 'gs://my_bucket/my_object'). Existing files will be overwritten.
     *
     * Generated from protobuf field <code>string uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Required. Data destination URI (e.g.
     * 'gs://my_bucket/my_object'). Existing files will be overwritten.
     *
     * Generated from protobuf field <code>string uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->uri = $var;

        return $this;
    }

}

