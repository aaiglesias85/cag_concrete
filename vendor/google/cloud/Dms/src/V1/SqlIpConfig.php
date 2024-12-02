<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/clouddms/v1/clouddms_resources.proto

namespace Google\Cloud\CloudDms\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * IP Management configuration.
 *
 * Generated from protobuf message <code>google.cloud.clouddms.v1.SqlIpConfig</code>
 */
class SqlIpConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * Whether the instance should be assigned an IPv4 address or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue enable_ipv4 = 1;</code>
     */
    private $enable_ipv4 = null;
    /**
     * The resource link for the VPC network from which the Cloud SQL instance is
     * accessible for private IP. For example,
     * `projects/myProject/global/networks/default`. This setting can
     * be updated, but it cannot be removed after it is set.
     *
     * Generated from protobuf field <code>string private_network = 2;</code>
     */
    private $private_network = '';
    /**
     * Whether SSL connections over IP should be enforced or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue require_ssl = 3;</code>
     */
    private $require_ssl = null;
    /**
     * The list of external networks that are allowed to connect to the instance
     * using the IP. See
     * https://en.wikipedia.org/wiki/CIDR_notation#CIDR_notation, also known as
     * 'slash' notation (e.g. `192.168.100.0/24`).
     *
     * Generated from protobuf field <code>repeated .google.cloud.clouddms.v1.SqlAclEntry authorized_networks = 4;</code>
     */
    private $authorized_networks;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Protobuf\BoolValue $enable_ipv4
     *           Whether the instance should be assigned an IPv4 address or not.
     *     @type string $private_network
     *           The resource link for the VPC network from which the Cloud SQL instance is
     *           accessible for private IP. For example,
     *           `projects/myProject/global/networks/default`. This setting can
     *           be updated, but it cannot be removed after it is set.
     *     @type \Google\Protobuf\BoolValue $require_ssl
     *           Whether SSL connections over IP should be enforced or not.
     *     @type \Google\Cloud\CloudDms\V1\SqlAclEntry[]|\Google\Protobuf\Internal\RepeatedField $authorized_networks
     *           The list of external networks that are allowed to connect to the instance
     *           using the IP. See
     *           https://en.wikipedia.org/wiki/CIDR_notation#CIDR_notation, also known as
     *           'slash' notation (e.g. `192.168.100.0/24`).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Clouddms\V1\ClouddmsResources::initOnce();
        parent::__construct($data);
    }

    /**
     * Whether the instance should be assigned an IPv4 address or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue enable_ipv4 = 1;</code>
     * @return \Google\Protobuf\BoolValue|null
     */
    public function getEnableIpv4()
    {
        return $this->enable_ipv4;
    }

    public function hasEnableIpv4()
    {
        return isset($this->enable_ipv4);
    }

    public function clearEnableIpv4()
    {
        unset($this->enable_ipv4);
    }

    /**
     * Returns the unboxed value from <code>getEnableIpv4()</code>

     * Whether the instance should be assigned an IPv4 address or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue enable_ipv4 = 1;</code>
     * @return bool|null
     */
    public function getEnableIpv4Value()
    {
        return $this->readWrapperValue("enable_ipv4");
    }

    /**
     * Whether the instance should be assigned an IPv4 address or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue enable_ipv4 = 1;</code>
     * @param \Google\Protobuf\BoolValue $var
     * @return $this
     */
    public function setEnableIpv4($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\BoolValue::class);
        $this->enable_ipv4 = $var;

        return $this;
    }

    /**
     * Sets the field by wrapping a primitive type in a Google\Protobuf\BoolValue object.

     * Whether the instance should be assigned an IPv4 address or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue enable_ipv4 = 1;</code>
     * @param bool|null $var
     * @return $this
     */
    public function setEnableIpv4Value($var)
    {
        $this->writeWrapperValue("enable_ipv4", $var);
        return $this;}

    /**
     * The resource link for the VPC network from which the Cloud SQL instance is
     * accessible for private IP. For example,
     * `projects/myProject/global/networks/default`. This setting can
     * be updated, but it cannot be removed after it is set.
     *
     * Generated from protobuf field <code>string private_network = 2;</code>
     * @return string
     */
    public function getPrivateNetwork()
    {
        return $this->private_network;
    }

    /**
     * The resource link for the VPC network from which the Cloud SQL instance is
     * accessible for private IP. For example,
     * `projects/myProject/global/networks/default`. This setting can
     * be updated, but it cannot be removed after it is set.
     *
     * Generated from protobuf field <code>string private_network = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setPrivateNetwork($var)
    {
        GPBUtil::checkString($var, True);
        $this->private_network = $var;

        return $this;
    }

    /**
     * Whether SSL connections over IP should be enforced or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue require_ssl = 3;</code>
     * @return \Google\Protobuf\BoolValue|null
     */
    public function getRequireSsl()
    {
        return $this->require_ssl;
    }

    public function hasRequireSsl()
    {
        return isset($this->require_ssl);
    }

    public function clearRequireSsl()
    {
        unset($this->require_ssl);
    }

    /**
     * Returns the unboxed value from <code>getRequireSsl()</code>

     * Whether SSL connections over IP should be enforced or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue require_ssl = 3;</code>
     * @return bool|null
     */
    public function getRequireSslValue()
    {
        return $this->readWrapperValue("require_ssl");
    }

    /**
     * Whether SSL connections over IP should be enforced or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue require_ssl = 3;</code>
     * @param \Google\Protobuf\BoolValue $var
     * @return $this
     */
    public function setRequireSsl($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\BoolValue::class);
        $this->require_ssl = $var;

        return $this;
    }

    /**
     * Sets the field by wrapping a primitive type in a Google\Protobuf\BoolValue object.

     * Whether SSL connections over IP should be enforced or not.
     *
     * Generated from protobuf field <code>.google.protobuf.BoolValue require_ssl = 3;</code>
     * @param bool|null $var
     * @return $this
     */
    public function setRequireSslValue($var)
    {
        $this->writeWrapperValue("require_ssl", $var);
        return $this;}

    /**
     * The list of external networks that are allowed to connect to the instance
     * using the IP. See
     * https://en.wikipedia.org/wiki/CIDR_notation#CIDR_notation, also known as
     * 'slash' notation (e.g. `192.168.100.0/24`).
     *
     * Generated from protobuf field <code>repeated .google.cloud.clouddms.v1.SqlAclEntry authorized_networks = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAuthorizedNetworks()
    {
        return $this->authorized_networks;
    }

    /**
     * The list of external networks that are allowed to connect to the instance
     * using the IP. See
     * https://en.wikipedia.org/wiki/CIDR_notation#CIDR_notation, also known as
     * 'slash' notation (e.g. `192.168.100.0/24`).
     *
     * Generated from protobuf field <code>repeated .google.cloud.clouddms.v1.SqlAclEntry authorized_networks = 4;</code>
     * @param \Google\Cloud\CloudDms\V1\SqlAclEntry[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAuthorizedNetworks($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\CloudDms\V1\SqlAclEntry::class);
        $this->authorized_networks = $arr;

        return $this;
    }

}

