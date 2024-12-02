<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/endpoint_service.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for [EndpointService.DeployModel][google.cloud.aiplatform.v1.EndpointService.DeployModel].
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.DeployModelResponse</code>
 */
class DeployModelResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The DeployedModel that had been deployed in the Endpoint.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.DeployedModel deployed_model = 1;</code>
     */
    private $deployed_model = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\AIPlatform\V1\DeployedModel $deployed_model
     *           The DeployedModel that had been deployed in the Endpoint.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\EndpointService::initOnce();
        parent::__construct($data);
    }

    /**
     * The DeployedModel that had been deployed in the Endpoint.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.DeployedModel deployed_model = 1;</code>
     * @return \Google\Cloud\AIPlatform\V1\DeployedModel|null
     */
    public function getDeployedModel()
    {
        return $this->deployed_model;
    }

    public function hasDeployedModel()
    {
        return isset($this->deployed_model);
    }

    public function clearDeployedModel()
    {
        unset($this->deployed_model);
    }

    /**
     * The DeployedModel that had been deployed in the Endpoint.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.DeployedModel deployed_model = 1;</code>
     * @param \Google\Cloud\AIPlatform\V1\DeployedModel $var
     * @return $this
     */
    public function setDeployedModel($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\DeployedModel::class);
        $this->deployed_model = $var;

        return $this;
    }

}

