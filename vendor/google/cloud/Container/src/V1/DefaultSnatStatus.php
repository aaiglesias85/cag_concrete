<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/container/v1/cluster_service.proto

namespace Google\Cloud\Container\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * DefaultSnatStatus contains the desired state of whether default sNAT should
 * be disabled on the cluster.
 *
 * Generated from protobuf message <code>google.container.v1.DefaultSnatStatus</code>
 */
class DefaultSnatStatus extends \Google\Protobuf\Internal\Message
{
    /**
     * Disables cluster default sNAT rules.
     *
     * Generated from protobuf field <code>bool disabled = 1;</code>
     */
    private $disabled = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $disabled
     *           Disables cluster default sNAT rules.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Container\V1\ClusterService::initOnce();
        parent::__construct($data);
    }

    /**
     * Disables cluster default sNAT rules.
     *
     * Generated from protobuf field <code>bool disabled = 1;</code>
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Disables cluster default sNAT rules.
     *
     * Generated from protobuf field <code>bool disabled = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setDisabled($var)
    {
        GPBUtil::checkBool($var);
        $this->disabled = $var;

        return $this;
    }

}

