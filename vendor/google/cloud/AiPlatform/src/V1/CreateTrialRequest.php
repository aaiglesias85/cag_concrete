<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/vizier_service.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for [VizierService.CreateTrial][google.cloud.aiplatform.v1.VizierService.CreateTrial].
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.CreateTrialRequest</code>
 */
class CreateTrialRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The resource name of the Study to create the Trial in.
     * Format: `projects/{project}/locations/{location}/studies/{study}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The Trial to create.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Trial trial = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $trial = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The resource name of the Study to create the Trial in.
     *           Format: `projects/{project}/locations/{location}/studies/{study}`
     *     @type \Google\Cloud\AIPlatform\V1\Trial $trial
     *           Required. The Trial to create.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\VizierService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The resource name of the Study to create the Trial in.
     * Format: `projects/{project}/locations/{location}/studies/{study}`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The resource name of the Study to create the Trial in.
     * Format: `projects/{project}/locations/{location}/studies/{study}`
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
     * Required. The Trial to create.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Trial trial = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\AIPlatform\V1\Trial|null
     */
    public function getTrial()
    {
        return $this->trial;
    }

    public function hasTrial()
    {
        return isset($this->trial);
    }

    public function clearTrial()
    {
        unset($this->trial);
    }

    /**
     * Required. The Trial to create.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Trial trial = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\AIPlatform\V1\Trial $var
     * @return $this
     */
    public function setTrial($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\Trial::class);
        $this->trial = $var;

        return $this;
    }

}

