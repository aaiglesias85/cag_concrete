<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/notebooks/v1beta1/service.proto

namespace Google\Cloud\Notebooks\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request for creating a notebook instance.
 *
 * Generated from protobuf message <code>google.cloud.notebooks.v1beta1.CreateInstanceRequest</code>
 */
class CreateInstanceRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Format:
     * `parent=projects/{project_id}/locations/{location}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $parent = '';
    /**
     * Required. User-defined unique ID of this instance.
     *
     * Generated from protobuf field <code>string instance_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $instance_id = '';
    /**
     * Required. The instance to be created.
     *
     * Generated from protobuf field <code>.google.cloud.notebooks.v1beta1.Instance instance = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $instance = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. Format:
     *           `parent=projects/{project_id}/locations/{location}`
     *     @type string $instance_id
     *           Required. User-defined unique ID of this instance.
     *     @type \Google\Cloud\Notebooks\V1beta1\Instance $instance
     *           Required. The instance to be created.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Notebooks\V1Beta1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Format:
     * `parent=projects/{project_id}/locations/{location}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. Format:
     * `parent=projects/{project_id}/locations/{location}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. User-defined unique ID of this instance.
     *
     * Generated from protobuf field <code>string instance_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instance_id;
    }

    /**
     * Required. User-defined unique ID of this instance.
     *
     * Generated from protobuf field <code>string instance_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setInstanceId($var)
    {
        GPBUtil::checkString($var, True);
        $this->instance_id = $var;

        return $this;
    }

    /**
     * Required. The instance to be created.
     *
     * Generated from protobuf field <code>.google.cloud.notebooks.v1beta1.Instance instance = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Notebooks\V1beta1\Instance|null
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function hasInstance()
    {
        return isset($this->instance);
    }

    public function clearInstance()
    {
        unset($this->instance);
    }

    /**
     * Required. The instance to be created.
     *
     * Generated from protobuf field <code>.google.cloud.notebooks.v1beta1.Instance instance = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Notebooks\V1beta1\Instance $var
     * @return $this
     */
    public function setInstance($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Notebooks\V1beta1\Instance::class);
        $this->instance = $var;

        return $this;
    }

}

