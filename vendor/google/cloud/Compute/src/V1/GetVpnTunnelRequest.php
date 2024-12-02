<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A request message for VpnTunnels.Get. See the method description for details.
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.GetVpnTunnelRequest</code>
 */
class GetVpnTunnelRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Project ID for this request.
     *
     * Generated from protobuf field <code>string project = 227560217 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $project = '';
    /**
     * Name of the region for this request.
     *
     * Generated from protobuf field <code>string region = 138946292 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $region = '';
    /**
     * Name of the VpnTunnel resource to return.
     *
     * Generated from protobuf field <code>string vpn_tunnel = 143821331 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $vpn_tunnel = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $project
     *           Project ID for this request.
     *     @type string $region
     *           Name of the region for this request.
     *     @type string $vpn_tunnel
     *           Name of the VpnTunnel resource to return.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * Project ID for this request.
     *
     * Generated from protobuf field <code>string project = 227560217 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Project ID for this request.
     *
     * Generated from protobuf field <code>string project = 227560217 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setProject($var)
    {
        GPBUtil::checkString($var, True);
        $this->project = $var;

        return $this;
    }

    /**
     * Name of the region for this request.
     *
     * Generated from protobuf field <code>string region = 138946292 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Name of the region for this request.
     *
     * Generated from protobuf field <code>string region = 138946292 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setRegion($var)
    {
        GPBUtil::checkString($var, True);
        $this->region = $var;

        return $this;
    }

    /**
     * Name of the VpnTunnel resource to return.
     *
     * Generated from protobuf field <code>string vpn_tunnel = 143821331 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getVpnTunnel()
    {
        return $this->vpn_tunnel;
    }

    /**
     * Name of the VpnTunnel resource to return.
     *
     * Generated from protobuf field <code>string vpn_tunnel = 143821331 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setVpnTunnel($var)
    {
        GPBUtil::checkString($var, True);
        $this->vpn_tunnel = $var;

        return $this;
    }

}

