<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datalabeling/v1beta1/dataset.proto

namespace Google\Cloud\DataLabeling\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata for classification annotations.
 *
 * Generated from protobuf message <code>google.cloud.datalabeling.v1beta1.ClassificationMetadata</code>
 */
class ClassificationMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Whether the classification task is multi-label or not.
     *
     * Generated from protobuf field <code>bool is_multi_label = 1;</code>
     */
    private $is_multi_label = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $is_multi_label
     *           Whether the classification task is multi-label or not.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datalabeling\V1Beta1\Dataset::initOnce();
        parent::__construct($data);
    }

    /**
     * Whether the classification task is multi-label or not.
     *
     * Generated from protobuf field <code>bool is_multi_label = 1;</code>
     * @return bool
     */
    public function getIsMultiLabel()
    {
        return $this->is_multi_label;
    }

    /**
     * Whether the classification task is multi-label or not.
     *
     * Generated from protobuf field <code>bool is_multi_label = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setIsMultiLabel($var)
    {
        GPBUtil::checkBool($var);
        $this->is_multi_label = $var;

        return $this;
    }

}

