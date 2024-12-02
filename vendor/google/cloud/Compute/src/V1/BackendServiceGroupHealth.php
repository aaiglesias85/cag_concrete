<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.BackendServiceGroupHealth</code>
 */
class BackendServiceGroupHealth extends \Google\Protobuf\Internal\Message
{
    /**
     * Metadata defined as annotations on the network endpoint group.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 112032548;</code>
     */
    private $annotations;
    /**
     * Health state of the backend instances or endpoints in requested instance or network endpoint group, determined based on configured health checks.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.HealthStatus health_status = 380545845;</code>
     */
    private $health_status;
    /**
     * [Output Only] Type of resource. Always compute#backendServiceGroupHealth for the health of backend services.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     */
    private $kind = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $annotations
     *           Metadata defined as annotations on the network endpoint group.
     *     @type \Google\Cloud\Compute\V1\HealthStatus[]|\Google\Protobuf\Internal\RepeatedField $health_status
     *           Health state of the backend instances or endpoints in requested instance or network endpoint group, determined based on configured health checks.
     *     @type string $kind
     *           [Output Only] Type of resource. Always compute#backendServiceGroupHealth for the health of backend services.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * Metadata defined as annotations on the network endpoint group.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 112032548;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Metadata defined as annotations on the network endpoint group.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 112032548;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setAnnotations($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->annotations = $arr;

        return $this;
    }

    /**
     * Health state of the backend instances or endpoints in requested instance or network endpoint group, determined based on configured health checks.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.HealthStatus health_status = 380545845;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getHealthStatus()
    {
        return $this->health_status;
    }

    /**
     * Health state of the backend instances or endpoints in requested instance or network endpoint group, determined based on configured health checks.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.HealthStatus health_status = 380545845;</code>
     * @param \Google\Cloud\Compute\V1\HealthStatus[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setHealthStatus($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Compute\V1\HealthStatus::class);
        $this->health_status = $arr;

        return $this;
    }

    /**
     * [Output Only] Type of resource. Always compute#backendServiceGroupHealth for the health of backend services.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     * @return string
     */
    public function getKind()
    {
        return isset($this->kind) ? $this->kind : '';
    }

    public function hasKind()
    {
        return isset($this->kind);
    }

    public function clearKind()
    {
        unset($this->kind);
    }

    /**
     * [Output Only] Type of resource. Always compute#backendServiceGroupHealth for the health of backend services.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     * @param string $var
     * @return $this
     */
    public function setKind($var)
    {
        GPBUtil::checkString($var, True);
        $this->kind = $var;

        return $this;
    }

}

