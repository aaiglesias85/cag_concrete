<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/iot/v1/resources.proto

namespace Google\Cloud\Iot\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The configuration for notification of new states received from the device.
 *
 * Generated from protobuf message <code>google.cloud.iot.v1.StateNotificationConfig</code>
 */
class StateNotificationConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * A Cloud Pub/Sub topic name. For example,
     * `projects/myProject/topics/deviceEvents`.
     *
     * Generated from protobuf field <code>string pubsub_topic_name = 1;</code>
     */
    private $pubsub_topic_name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $pubsub_topic_name
     *           A Cloud Pub/Sub topic name. For example,
     *           `projects/myProject/topics/deviceEvents`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Iot\V1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * A Cloud Pub/Sub topic name. For example,
     * `projects/myProject/topics/deviceEvents`.
     *
     * Generated from protobuf field <code>string pubsub_topic_name = 1;</code>
     * @return string
     */
    public function getPubsubTopicName()
    {
        return $this->pubsub_topic_name;
    }

    /**
     * A Cloud Pub/Sub topic name. For example,
     * `projects/myProject/topics/deviceEvents`.
     *
     * Generated from protobuf field <code>string pubsub_topic_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setPubsubTopicName($var)
    {
        GPBUtil::checkString($var, True);
        $this->pubsub_topic_name = $var;

        return $this;
    }

}

