<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datastream/v1/datastream_resources.proto

namespace Google\Cloud\Datastream\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A validation to perform on a stream.
 *
 * Generated from protobuf message <code>google.cloud.datastream.v1.Validation</code>
 */
class Validation extends \Google\Protobuf\Internal\Message
{
    /**
     * A short description of the validation.
     *
     * Generated from protobuf field <code>string description = 1;</code>
     */
    private $description = '';
    /**
     * Validation execution status.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.Validation.State state = 2;</code>
     */
    private $state = 0;
    /**
     * Messages reflecting the validation results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.ValidationMessage message = 3;</code>
     */
    private $message;
    /**
     * A custom code identifying this validation.
     *
     * Generated from protobuf field <code>string code = 4;</code>
     */
    private $code = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $description
     *           A short description of the validation.
     *     @type int $state
     *           Validation execution status.
     *     @type \Google\Cloud\Datastream\V1\ValidationMessage[]|\Google\Protobuf\Internal\RepeatedField $message
     *           Messages reflecting the validation results.
     *     @type string $code
     *           A custom code identifying this validation.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datastream\V1\DatastreamResources::initOnce();
        parent::__construct($data);
    }

    /**
     * A short description of the validation.
     *
     * Generated from protobuf field <code>string description = 1;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * A short description of the validation.
     *
     * Generated from protobuf field <code>string description = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * Validation execution status.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.Validation.State state = 2;</code>
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Validation execution status.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.Validation.State state = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setState($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Datastream\V1\Validation\State::class);
        $this->state = $var;

        return $this;
    }

    /**
     * Messages reflecting the validation results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.ValidationMessage message = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Messages reflecting the validation results.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.ValidationMessage message = 3;</code>
     * @param \Google\Cloud\Datastream\V1\ValidationMessage[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setMessage($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Datastream\V1\ValidationMessage::class);
        $this->message = $arr;

        return $this;
    }

    /**
     * A custom code identifying this validation.
     *
     * Generated from protobuf field <code>string code = 4;</code>
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * A custom code identifying this validation.
     *
     * Generated from protobuf field <code>string code = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->code = $var;

        return $this;
    }

}

