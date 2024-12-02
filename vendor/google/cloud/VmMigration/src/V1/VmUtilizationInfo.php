<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vmmigration/v1/vmmigration.proto

namespace Google\Cloud\VMMigration\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Utilization information of a single VM.
 *
 * Generated from protobuf message <code>google.cloud.vmmigration.v1.VmUtilizationInfo</code>
 */
class VmUtilizationInfo extends \Google\Protobuf\Internal\Message
{
    /**
     * The VM's ID in the source.
     *
     * Generated from protobuf field <code>string vm_id = 3;</code>
     */
    private $vm_id = '';
    /**
     * Utilization metrics for this VM.
     *
     * Generated from protobuf field <code>.google.cloud.vmmigration.v1.VmUtilizationMetrics utilization = 2;</code>
     */
    private $utilization = null;
    protected $VmDetails;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\VMMigration\V1\VmwareVmDetails $vmware_vm_details
     *           The description of the VM in a Source of type Vmware.
     *     @type string $vm_id
     *           The VM's ID in the source.
     *     @type \Google\Cloud\VMMigration\V1\VmUtilizationMetrics $utilization
     *           Utilization metrics for this VM.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Vmmigration\V1\Vmmigration::initOnce();
        parent::__construct($data);
    }

    /**
     * The description of the VM in a Source of type Vmware.
     *
     * Generated from protobuf field <code>.google.cloud.vmmigration.v1.VmwareVmDetails vmware_vm_details = 1;</code>
     * @return \Google\Cloud\VMMigration\V1\VmwareVmDetails|null
     */
    public function getVmwareVmDetails()
    {
        return $this->readOneof(1);
    }

    public function hasVmwareVmDetails()
    {
        return $this->hasOneof(1);
    }

    /**
     * The description of the VM in a Source of type Vmware.
     *
     * Generated from protobuf field <code>.google.cloud.vmmigration.v1.VmwareVmDetails vmware_vm_details = 1;</code>
     * @param \Google\Cloud\VMMigration\V1\VmwareVmDetails $var
     * @return $this
     */
    public function setVmwareVmDetails($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\VMMigration\V1\VmwareVmDetails::class);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * The VM's ID in the source.
     *
     * Generated from protobuf field <code>string vm_id = 3;</code>
     * @return string
     */
    public function getVmId()
    {
        return $this->vm_id;
    }

    /**
     * The VM's ID in the source.
     *
     * Generated from protobuf field <code>string vm_id = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setVmId($var)
    {
        GPBUtil::checkString($var, True);
        $this->vm_id = $var;

        return $this;
    }

    /**
     * Utilization metrics for this VM.
     *
     * Generated from protobuf field <code>.google.cloud.vmmigration.v1.VmUtilizationMetrics utilization = 2;</code>
     * @return \Google\Cloud\VMMigration\V1\VmUtilizationMetrics|null
     */
    public function getUtilization()
    {
        return $this->utilization;
    }

    public function hasUtilization()
    {
        return isset($this->utilization);
    }

    public function clearUtilization()
    {
        unset($this->utilization);
    }

    /**
     * Utilization metrics for this VM.
     *
     * Generated from protobuf field <code>.google.cloud.vmmigration.v1.VmUtilizationMetrics utilization = 2;</code>
     * @param \Google\Cloud\VMMigration\V1\VmUtilizationMetrics $var
     * @return $this
     */
    public function setUtilization($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\VMMigration\V1\VmUtilizationMetrics::class);
        $this->utilization = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getVmDetails()
    {
        return $this->whichOneof("VmDetails");
    }

}

