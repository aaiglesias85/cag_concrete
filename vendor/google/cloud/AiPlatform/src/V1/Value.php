<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/value.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Value is the value of the field.
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.Value</code>
 */
class Value extends \Google\Protobuf\Internal\Message
{
    protected $value;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $int_value
     *           An integer value.
     *     @type float $double_value
     *           A double value.
     *     @type string $string_value
     *           A string value.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\Value::initOnce();
        parent::__construct($data);
    }

    /**
     * An integer value.
     *
     * Generated from protobuf field <code>int64 int_value = 1;</code>
     * @return int|string
     */
    public function getIntValue()
    {
        return $this->readOneof(1);
    }

    public function hasIntValue()
    {
        return $this->hasOneof(1);
    }

    /**
     * An integer value.
     *
     * Generated from protobuf field <code>int64 int_value = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setIntValue($var)
    {
        GPBUtil::checkInt64($var);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * A double value.
     *
     * Generated from protobuf field <code>double double_value = 2;</code>
     * @return float
     */
    public function getDoubleValue()
    {
        return $this->readOneof(2);
    }

    public function hasDoubleValue()
    {
        return $this->hasOneof(2);
    }

    /**
     * A double value.
     *
     * Generated from protobuf field <code>double double_value = 2;</code>
     * @param float $var
     * @return $this
     */
    public function setDoubleValue($var)
    {
        GPBUtil::checkDouble($var);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * A string value.
     *
     * Generated from protobuf field <code>string string_value = 3;</code>
     * @return string
     */
    public function getStringValue()
    {
        return $this->readOneof(3);
    }

    public function hasStringValue()
    {
        return $this->hasOneof(3);
    }

    /**
     * A string value.
     *
     * Generated from protobuf field <code>string string_value = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setStringValue($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(3, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->whichOneof("value");
    }

}

