<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.RegionInstanceGroupsListInstancesRequest</code>
 */
class RegionInstanceGroupsListInstancesRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Instances in which state should be returned. Valid options are: 'ALL', 'RUNNING'. By default, it lists all instances.
     * Check the InstanceState enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string instance_state = 92223591;</code>
     */
    private $instance_state = null;
    /**
     * Name of port user is interested in. It is optional. If it is set, only information about this ports will be returned. If it is not set, all the named ports will be returned. Always lists all instances.
     *
     * Generated from protobuf field <code>optional string port_name = 41534345;</code>
     */
    private $port_name = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $instance_state
     *           Instances in which state should be returned. Valid options are: 'ALL', 'RUNNING'. By default, it lists all instances.
     *           Check the InstanceState enum for the list of possible values.
     *     @type string $port_name
     *           Name of port user is interested in. It is optional. If it is set, only information about this ports will be returned. If it is not set, all the named ports will be returned. Always lists all instances.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * Instances in which state should be returned. Valid options are: 'ALL', 'RUNNING'. By default, it lists all instances.
     * Check the InstanceState enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string instance_state = 92223591;</code>
     * @return string
     */
    public function getInstanceState()
    {
        return isset($this->instance_state) ? $this->instance_state : '';
    }

    public function hasInstanceState()
    {
        return isset($this->instance_state);
    }

    public function clearInstanceState()
    {
        unset($this->instance_state);
    }

    /**
     * Instances in which state should be returned. Valid options are: 'ALL', 'RUNNING'. By default, it lists all instances.
     * Check the InstanceState enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string instance_state = 92223591;</code>
     * @param string $var
     * @return $this
     */
    public function setInstanceState($var)
    {
        GPBUtil::checkString($var, True);
        $this->instance_state = $var;

        return $this;
    }

    /**
     * Name of port user is interested in. It is optional. If it is set, only information about this ports will be returned. If it is not set, all the named ports will be returned. Always lists all instances.
     *
     * Generated from protobuf field <code>optional string port_name = 41534345;</code>
     * @return string
     */
    public function getPortName()
    {
        return isset($this->port_name) ? $this->port_name : '';
    }

    public function hasPortName()
    {
        return isset($this->port_name);
    }

    public function clearPortName()
    {
        unset($this->port_name);
    }

    /**
     * Name of port user is interested in. It is optional. If it is set, only information about this ports will be returned. If it is not set, all the named ports will be returned. Always lists all instances.
     *
     * Generated from protobuf field <code>optional string port_name = 41534345;</code>
     * @param string $var
     * @return $this
     */
    public function setPortName($var)
    {
        GPBUtil::checkString($var, True);
        $this->port_name = $var;

        return $this;
    }

}

