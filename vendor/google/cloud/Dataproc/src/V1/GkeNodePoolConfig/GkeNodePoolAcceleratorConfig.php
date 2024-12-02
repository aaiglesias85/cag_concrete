<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dataproc/v1/shared.proto

namespace Google\Cloud\Dataproc\V1\GkeNodePoolConfig;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A GkeNodeConfigAcceleratorConfig represents a Hardware Accelerator request
 * for a NodePool.
 *
 * Generated from protobuf message <code>google.cloud.dataproc.v1.GkeNodePoolConfig.GkeNodePoolAcceleratorConfig</code>
 */
class GkeNodePoolAcceleratorConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * The number of accelerator cards exposed to an instance.
     *
     * Generated from protobuf field <code>int64 accelerator_count = 1;</code>
     */
    private $accelerator_count = 0;
    /**
     * The accelerator type resource namename (see GPUs on Compute Engine).
     *
     * Generated from protobuf field <code>string accelerator_type = 2;</code>
     */
    private $accelerator_type = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $accelerator_count
     *           The number of accelerator cards exposed to an instance.
     *     @type string $accelerator_type
     *           The accelerator type resource namename (see GPUs on Compute Engine).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dataproc\V1\Shared::initOnce();
        parent::__construct($data);
    }

    /**
     * The number of accelerator cards exposed to an instance.
     *
     * Generated from protobuf field <code>int64 accelerator_count = 1;</code>
     * @return int|string
     */
    public function getAcceleratorCount()
    {
        return $this->accelerator_count;
    }

    /**
     * The number of accelerator cards exposed to an instance.
     *
     * Generated from protobuf field <code>int64 accelerator_count = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setAcceleratorCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->accelerator_count = $var;

        return $this;
    }

    /**
     * The accelerator type resource namename (see GPUs on Compute Engine).
     *
     * Generated from protobuf field <code>string accelerator_type = 2;</code>
     * @return string
     */
    public function getAcceleratorType()
    {
        return $this->accelerator_type;
    }

    /**
     * The accelerator type resource namename (see GPUs on Compute Engine).
     *
     * Generated from protobuf field <code>string accelerator_type = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setAcceleratorType($var)
    {
        GPBUtil::checkString($var, True);
        $this->accelerator_type = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GkeNodePoolAcceleratorConfig::class, \Google\Cloud\Dataproc\V1\GkeNodePoolConfig_GkeNodePoolAcceleratorConfig::class);

