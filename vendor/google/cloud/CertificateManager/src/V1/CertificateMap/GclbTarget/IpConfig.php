<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/certificatemanager/v1/certificate_manager.proto

namespace Google\Cloud\CertificateManager\V1\CertificateMap\GclbTarget;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Defines IP configuration where this Certificate Map is serving.
 *
 * Generated from protobuf message <code>google.cloud.certificatemanager.v1.CertificateMap.GclbTarget.IpConfig</code>
 */
class IpConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * An external IP address.
     *
     * Generated from protobuf field <code>string ip_address = 1;</code>
     */
    private $ip_address = '';
    /**
     * Ports.
     *
     * Generated from protobuf field <code>repeated uint32 ports = 3;</code>
     */
    private $ports;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $ip_address
     *           An external IP address.
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $ports
     *           Ports.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Certificatemanager\V1\CertificateManager::initOnce();
        parent::__construct($data);
    }

    /**
     * An external IP address.
     *
     * Generated from protobuf field <code>string ip_address = 1;</code>
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * An external IP address.
     *
     * Generated from protobuf field <code>string ip_address = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setIpAddress($var)
    {
        GPBUtil::checkString($var, True);
        $this->ip_address = $var;

        return $this;
    }

    /**
     * Ports.
     *
     * Generated from protobuf field <code>repeated uint32 ports = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPorts()
    {
        return $this->ports;
    }

    /**
     * Ports.
     *
     * Generated from protobuf field <code>repeated uint32 ports = 3;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPorts($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::UINT32);
        $this->ports = $arr;

        return $this;
    }

}


