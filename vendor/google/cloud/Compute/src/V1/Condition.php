<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * This is deprecated and has no effect. Do not use.
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.Condition</code>
 */
class Condition extends \Google\Protobuf\Internal\Message
{
    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Iam enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string iam = 104021;</code>
     */
    private $iam = null;
    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Op enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string op = 3553;</code>
     */
    private $op = null;
    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>optional string svc = 114272;</code>
     */
    private $svc = null;
    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Sys enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string sys = 114381;</code>
     */
    private $sys = null;
    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>repeated string values = 249928994;</code>
     */
    private $values;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $iam
     *           This is deprecated and has no effect. Do not use.
     *           Check the Iam enum for the list of possible values.
     *     @type string $op
     *           This is deprecated and has no effect. Do not use.
     *           Check the Op enum for the list of possible values.
     *     @type string $svc
     *           This is deprecated and has no effect. Do not use.
     *     @type string $sys
     *           This is deprecated and has no effect. Do not use.
     *           Check the Sys enum for the list of possible values.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $values
     *           This is deprecated and has no effect. Do not use.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Iam enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string iam = 104021;</code>
     * @return string
     */
    public function getIam()
    {
        return isset($this->iam) ? $this->iam : '';
    }

    public function hasIam()
    {
        return isset($this->iam);
    }

    public function clearIam()
    {
        unset($this->iam);
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Iam enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string iam = 104021;</code>
     * @param string $var
     * @return $this
     */
    public function setIam($var)
    {
        GPBUtil::checkString($var, True);
        $this->iam = $var;

        return $this;
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Op enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string op = 3553;</code>
     * @return string
     */
    public function getOp()
    {
        return isset($this->op) ? $this->op : '';
    }

    public function hasOp()
    {
        return isset($this->op);
    }

    public function clearOp()
    {
        unset($this->op);
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Op enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string op = 3553;</code>
     * @param string $var
     * @return $this
     */
    public function setOp($var)
    {
        GPBUtil::checkString($var, True);
        $this->op = $var;

        return $this;
    }

    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>optional string svc = 114272;</code>
     * @return string
     */
    public function getSvc()
    {
        return isset($this->svc) ? $this->svc : '';
    }

    public function hasSvc()
    {
        return isset($this->svc);
    }

    public function clearSvc()
    {
        unset($this->svc);
    }

    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>optional string svc = 114272;</code>
     * @param string $var
     * @return $this
     */
    public function setSvc($var)
    {
        GPBUtil::checkString($var, True);
        $this->svc = $var;

        return $this;
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Sys enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string sys = 114381;</code>
     * @return string
     */
    public function getSys()
    {
        return isset($this->sys) ? $this->sys : '';
    }

    public function hasSys()
    {
        return isset($this->sys);
    }

    public function clearSys()
    {
        unset($this->sys);
    }

    /**
     * This is deprecated and has no effect. Do not use.
     * Check the Sys enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string sys = 114381;</code>
     * @param string $var
     * @return $this
     */
    public function setSys($var)
    {
        GPBUtil::checkString($var, True);
        $this->sys = $var;

        return $this;
    }

    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>repeated string values = 249928994;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * This is deprecated and has no effect. Do not use.
     *
     * Generated from protobuf field <code>repeated string values = 249928994;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setValues($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->values = $arr;

        return $this;
    }

}

