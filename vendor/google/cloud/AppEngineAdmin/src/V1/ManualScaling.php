<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/appengine/v1/version.proto

namespace Google\Cloud\AppEngine\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A service with manual scaling runs continuously, allowing you to perform
 * complex initialization and rely on the state of its memory over time.
 *
 * Generated from protobuf message <code>google.appengine.v1.ManualScaling</code>
 */
class ManualScaling extends \Google\Protobuf\Internal\Message
{
    /**
     * Number of instances to assign to the service at the start. This number
     * can later be altered by using the
     * [Modules API](https://cloud.google.com/appengine/docs/python/modules/functions)
     * `set_num_instances()` function.
     *
     * Generated from protobuf field <code>int32 instances = 1;</code>
     */
    private $instances = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $instances
     *           Number of instances to assign to the service at the start. This number
     *           can later be altered by using the
     *           [Modules API](https://cloud.google.com/appengine/docs/python/modules/functions)
     *           `set_num_instances()` function.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Appengine\V1\Version::initOnce();
        parent::__construct($data);
    }

    /**
     * Number of instances to assign to the service at the start. This number
     * can later be altered by using the
     * [Modules API](https://cloud.google.com/appengine/docs/python/modules/functions)
     * `set_num_instances()` function.
     *
     * Generated from protobuf field <code>int32 instances = 1;</code>
     * @return int
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Number of instances to assign to the service at the start. This number
     * can later be altered by using the
     * [Modules API](https://cloud.google.com/appengine/docs/python/modules/functions)
     * `set_num_instances()` function.
     *
     * Generated from protobuf field <code>int32 instances = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setInstances($var)
    {
        GPBUtil::checkInt32($var);
        $this->instances = $var;

        return $this;
    }

}

