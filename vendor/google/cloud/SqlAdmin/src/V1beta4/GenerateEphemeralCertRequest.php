<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/sql/v1beta4/cloud_sql_connect.proto

namespace Google\Cloud\Sql\V1beta4;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Ephemeral certificate creation request.
 *
 * Generated from protobuf message <code>google.cloud.sql.v1beta4.GenerateEphemeralCertRequest</code>
 */
class GenerateEphemeralCertRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     */
    private $instance = '';
    /**
     * Project ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
     */
    private $project = '';
    /**
     * PEM encoded public key to include in the signed certificate.
     *
     * Generated from protobuf field <code>string public_key = 3;</code>
     */
    private $public_key = '';
    /**
     * Optional. Access token to include in the signed certificate.
     *
     * Generated from protobuf field <code>string access_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $access_token = '';
    /**
     * Optional. Optional snapshot read timestamp to trade freshness for performance.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp read_time = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $read_time = null;
    /**
     * Optional. If set, it will contain the cert valid duration.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration valid_duration = 12 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $valid_duration = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $instance
     *           Cloud SQL instance ID. This does not include the project ID.
     *     @type string $project
     *           Project ID of the project that contains the instance.
     *     @type string $public_key
     *           PEM encoded public key to include in the signed certificate.
     *     @type string $access_token
     *           Optional. Access token to include in the signed certificate.
     *     @type \Google\Protobuf\Timestamp $read_time
     *           Optional. Optional snapshot read timestamp to trade freshness for performance.
     *     @type \Google\Protobuf\Duration $valid_duration
     *           Optional. If set, it will contain the cert valid duration.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Sql\V1Beta4\CloudSqlConnect::initOnce();
        parent::__construct($data);
    }

    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setInstance($var)
    {
        GPBUtil::checkString($var, True);
        $this->instance = $var;

        return $this;
    }

    /**
     * Project ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Project ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
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
     * PEM encoded public key to include in the signed certificate.
     *
     * Generated from protobuf field <code>string public_key = 3;</code>
     * @return string
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * PEM encoded public key to include in the signed certificate.
     *
     * Generated from protobuf field <code>string public_key = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setPublicKey($var)
    {
        GPBUtil::checkString($var, True);
        $this->public_key = $var;

        return $this;
    }

    /**
     * Optional. Access token to include in the signed certificate.
     *
     * Generated from protobuf field <code>string access_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Optional. Access token to include in the signed certificate.
     *
     * Generated from protobuf field <code>string access_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setAccessToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->access_token = $var;

        return $this;
    }

    /**
     * Optional. Optional snapshot read timestamp to trade freshness for performance.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp read_time = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getReadTime()
    {
        return $this->read_time;
    }

    public function hasReadTime()
    {
        return isset($this->read_time);
    }

    public function clearReadTime()
    {
        unset($this->read_time);
    }

    /**
     * Optional. Optional snapshot read timestamp to trade freshness for performance.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp read_time = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setReadTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->read_time = $var;

        return $this;
    }

    /**
     * Optional. If set, it will contain the cert valid duration.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration valid_duration = 12 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getValidDuration()
    {
        return $this->valid_duration;
    }

    public function hasValidDuration()
    {
        return isset($this->valid_duration);
    }

    public function clearValidDuration()
    {
        unset($this->valid_duration);
    }

    /**
     * Optional. If set, it will contain the cert valid duration.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration valid_duration = 12 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setValidDuration($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->valid_duration = $var;

        return $this;
    }

}

