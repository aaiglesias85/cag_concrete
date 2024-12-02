<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/osconfig/v1/osconfig_common.proto

namespace Google\Cloud\OsConfig\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Message encapsulating a value that can be either absolute ("fixed") or
 * relative ("percent") to a value.
 *
 * Generated from protobuf message <code>google.cloud.osconfig.v1.FixedOrPercent</code>
 */
class FixedOrPercent extends \Google\Protobuf\Internal\Message
{
    protected $mode;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $fixed
     *           Specifies a fixed value.
     *     @type int $percent
     *           Specifies the relative value defined as a percentage, which will be
     *           multiplied by a reference value.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Osconfig\V1\OsconfigCommon::initOnce();
        parent::__construct($data);
    }

    /**
     * Specifies a fixed value.
     *
     * Generated from protobuf field <code>int32 fixed = 1;</code>
     * @return int
     */
    public function getFixed()
    {
        return $this->readOneof(1);
    }

    public function hasFixed()
    {
        return $this->hasOneof(1);
    }

    /**
     * Specifies a fixed value.
     *
     * Generated from protobuf field <code>int32 fixed = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setFixed($var)
    {
        GPBUtil::checkInt32($var);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * Specifies the relative value defined as a percentage, which will be
     * multiplied by a reference value.
     *
     * Generated from protobuf field <code>int32 percent = 2;</code>
     * @return int
     */
    public function getPercent()
    {
        return $this->readOneof(2);
    }

    public function hasPercent()
    {
        return $this->hasOneof(2);
    }

    /**
     * Specifies the relative value defined as a percentage, which will be
     * multiplied by a reference value.
     *
     * Generated from protobuf field <code>int32 percent = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setPercent($var)
    {
        GPBUtil::checkInt32($var);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->whichOneof("mode");
    }

}

