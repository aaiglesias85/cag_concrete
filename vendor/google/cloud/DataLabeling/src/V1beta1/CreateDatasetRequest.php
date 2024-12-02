<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datalabeling/v1beta1/data_labeling_service.proto

namespace Google\Cloud\DataLabeling\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for CreateDataset.
 *
 * Generated from protobuf message <code>google.cloud.datalabeling.v1beta1.CreateDatasetRequest</code>
 */
class CreateDatasetRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Dataset resource parent, format:
     * projects/{project_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The dataset to be created.
     *
     * Generated from protobuf field <code>.google.cloud.datalabeling.v1beta1.Dataset dataset = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $dataset = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. Dataset resource parent, format:
     *           projects/{project_id}
     *     @type \Google\Cloud\DataLabeling\V1beta1\Dataset $dataset
     *           Required. The dataset to be created.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datalabeling\V1Beta1\DataLabelingService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Dataset resource parent, format:
     * projects/{project_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. Dataset resource parent, format:
     * projects/{project_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
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
     * Required. The dataset to be created.
     *
     * Generated from protobuf field <code>.google.cloud.datalabeling.v1beta1.Dataset dataset = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\DataLabeling\V1beta1\Dataset|null
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    public function hasDataset()
    {
        return isset($this->dataset);
    }

    public function clearDataset()
    {
        unset($this->dataset);
    }

    /**
     * Required. The dataset to be created.
     *
     * Generated from protobuf field <code>.google.cloud.datalabeling.v1beta1.Dataset dataset = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\DataLabeling\V1beta1\Dataset $var
     * @return $this
     */
    public function setDataset($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\DataLabeling\V1beta1\Dataset::class);
        $this->dataset = $var;

        return $this;
    }

}

