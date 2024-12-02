<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datalabeling/v1beta1/data_labeling_service.proto

namespace Google\Cloud\DataLabeling\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Results of listing instructions under a project.
 *
 * Generated from protobuf message <code>google.cloud.datalabeling.v1beta1.ListInstructionsResponse</code>
 */
class ListInstructionsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The list of Instructions to return.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datalabeling.v1beta1.Instruction instructions = 1;</code>
     */
    private $instructions;
    /**
     * A token to retrieve next page of results.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\DataLabeling\V1beta1\Instruction[]|\Google\Protobuf\Internal\RepeatedField $instructions
     *           The list of Instructions to return.
     *     @type string $next_page_token
     *           A token to retrieve next page of results.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datalabeling\V1Beta1\DataLabelingService::initOnce();
        parent::__construct($data);
    }

    /**
     * The list of Instructions to return.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datalabeling.v1beta1.Instruction instructions = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * The list of Instructions to return.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datalabeling.v1beta1.Instruction instructions = 1;</code>
     * @param \Google\Cloud\DataLabeling\V1beta1\Instruction[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInstructions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\DataLabeling\V1beta1\Instruction::class);
        $this->instructions = $arr;

        return $this;
    }

    /**
     * A token to retrieve next page of results.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * A token to retrieve next page of results.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}

