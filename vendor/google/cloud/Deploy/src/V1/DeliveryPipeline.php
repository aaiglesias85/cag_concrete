<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/deploy/v1/cloud_deploy.proto

namespace Google\Cloud\Deploy\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A `DeliveryPipeline` resource in the Google Cloud Deploy API.
 * A `DeliveryPipeline` defines a pipeline through which a Skaffold
 * configuration can progress.
 *
 * Generated from protobuf message <code>google.cloud.deploy.v1.DeliveryPipeline</code>
 */
class DeliveryPipeline extends \Google\Protobuf\Internal\Message
{
    /**
     * Optional. Name of the `DeliveryPipeline`. Format is projects/{project}/
     * locations/{location}/deliveryPipelines/[a-z][a-z0-9\-]{0,62}.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $name = '';
    /**
     * Output only. Unique identifier of the `DeliveryPipeline`.
     *
     * Generated from protobuf field <code>string uid = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $uid = '';
    /**
     * Description of the `DeliveryPipeline`. Max length is 255 characters.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     */
    private $description = '';
    /**
     * User annotations. These attributes can only be set and used by the
     * user, and not by Google Cloud Deploy. See
     * https://google.aip.dev/128#annotations for more details such as format and
     * size limitations.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 4;</code>
     */
    private $annotations;
    /**
     * Labels are attributes that can be set and used by both the
     * user and by Google Cloud Deploy. Labels must meet the following
     * constraints:
     * * Keys and values can contain only lowercase letters, numeric characters,
     * underscores, and dashes.
     * * All characters must use UTF-8 encoding, and international characters are
     * allowed.
     * * Keys must start with a lowercase letter or international character.
     * * Each resource is limited to a maximum of 64 labels.
     * Both keys and values are additionally constrained to be <= 128 bytes.
     *
     * Generated from protobuf field <code>map<string, string> labels = 5;</code>
     */
    private $labels;
    /**
     * Output only. Time at which the pipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. Most recent time at which the pipeline was updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $update_time = null;
    /**
     * Output only. Information around the state of the Delivery Pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.deploy.v1.PipelineCondition condition = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $condition = null;
    /**
     * This checksum is computed by the server based on the value of other
     * fields, and may be sent on update and delete requests to ensure the
     * client has an up-to-date value before proceeding.
     *
     * Generated from protobuf field <code>string etag = 10;</code>
     */
    private $etag = '';
    protected $pipeline;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Optional. Name of the `DeliveryPipeline`. Format is projects/{project}/
     *           locations/{location}/deliveryPipelines/[a-z][a-z0-9\-]{0,62}.
     *     @type string $uid
     *           Output only. Unique identifier of the `DeliveryPipeline`.
     *     @type string $description
     *           Description of the `DeliveryPipeline`. Max length is 255 characters.
     *     @type array|\Google\Protobuf\Internal\MapField $annotations
     *           User annotations. These attributes can only be set and used by the
     *           user, and not by Google Cloud Deploy. See
     *           https://google.aip.dev/128#annotations for more details such as format and
     *           size limitations.
     *     @type array|\Google\Protobuf\Internal\MapField $labels
     *           Labels are attributes that can be set and used by both the
     *           user and by Google Cloud Deploy. Labels must meet the following
     *           constraints:
     *           * Keys and values can contain only lowercase letters, numeric characters,
     *           underscores, and dashes.
     *           * All characters must use UTF-8 encoding, and international characters are
     *           allowed.
     *           * Keys must start with a lowercase letter or international character.
     *           * Each resource is limited to a maximum of 64 labels.
     *           Both keys and values are additionally constrained to be <= 128 bytes.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Time at which the pipeline was created.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Output only. Most recent time at which the pipeline was updated.
     *     @type \Google\Cloud\Deploy\V1\SerialPipeline $serial_pipeline
     *           SerialPipeline defines a sequential set of stages for a
     *           `DeliveryPipeline`.
     *     @type \Google\Cloud\Deploy\V1\PipelineCondition $condition
     *           Output only. Information around the state of the Delivery Pipeline.
     *     @type string $etag
     *           This checksum is computed by the server based on the value of other
     *           fields, and may be sent on update and delete requests to ensure the
     *           client has an up-to-date value before proceeding.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Deploy\V1\CloudDeploy::initOnce();
        parent::__construct($data);
    }

    /**
     * Optional. Name of the `DeliveryPipeline`. Format is projects/{project}/
     * locations/{location}/deliveryPipelines/[a-z][a-z0-9\-]{0,62}.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Optional. Name of the `DeliveryPipeline`. Format is projects/{project}/
     * locations/{location}/deliveryPipelines/[a-z][a-z0-9\-]{0,62}.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Output only. Unique identifier of the `DeliveryPipeline`.
     *
     * Generated from protobuf field <code>string uid = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Output only. Unique identifier of the `DeliveryPipeline`.
     *
     * Generated from protobuf field <code>string uid = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setUid($var)
    {
        GPBUtil::checkString($var, True);
        $this->uid = $var;

        return $this;
    }

    /**
     * Description of the `DeliveryPipeline`. Max length is 255 characters.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Description of the `DeliveryPipeline`. Max length is 255 characters.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * User annotations. These attributes can only be set and used by the
     * user, and not by Google Cloud Deploy. See
     * https://google.aip.dev/128#annotations for more details such as format and
     * size limitations.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 4;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * User annotations. These attributes can only be set and used by the
     * user, and not by Google Cloud Deploy. See
     * https://google.aip.dev/128#annotations for more details such as format and
     * size limitations.
     *
     * Generated from protobuf field <code>map<string, string> annotations = 4;</code>
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
     * Labels are attributes that can be set and used by both the
     * user and by Google Cloud Deploy. Labels must meet the following
     * constraints:
     * * Keys and values can contain only lowercase letters, numeric characters,
     * underscores, and dashes.
     * * All characters must use UTF-8 encoding, and international characters are
     * allowed.
     * * Keys must start with a lowercase letter or international character.
     * * Each resource is limited to a maximum of 64 labels.
     * Both keys and values are additionally constrained to be <= 128 bytes.
     *
     * Generated from protobuf field <code>map<string, string> labels = 5;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Labels are attributes that can be set and used by both the
     * user and by Google Cloud Deploy. Labels must meet the following
     * constraints:
     * * Keys and values can contain only lowercase letters, numeric characters,
     * underscores, and dashes.
     * * All characters must use UTF-8 encoding, and international characters are
     * allowed.
     * * Keys must start with a lowercase letter or international character.
     * * Each resource is limited to a maximum of 64 labels.
     * Both keys and values are additionally constrained to be <= 128 bytes.
     *
     * Generated from protobuf field <code>map<string, string> labels = 5;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setLabels($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->labels = $arr;

        return $this;
    }

    /**
     * Output only. Time at which the pipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    public function hasCreateTime()
    {
        return isset($this->create_time);
    }

    public function clearCreateTime()
    {
        unset($this->create_time);
    }

    /**
     * Output only. Time at which the pipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Output only. Most recent time at which the pipeline was updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    public function hasUpdateTime()
    {
        return isset($this->update_time);
    }

    public function clearUpdateTime()
    {
        unset($this->update_time);
    }

    /**
     * Output only. Most recent time at which the pipeline was updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setUpdateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->update_time = $var;

        return $this;
    }

    /**
     * SerialPipeline defines a sequential set of stages for a
     * `DeliveryPipeline`.
     *
     * Generated from protobuf field <code>.google.cloud.deploy.v1.SerialPipeline serial_pipeline = 8;</code>
     * @return \Google\Cloud\Deploy\V1\SerialPipeline|null
     */
    public function getSerialPipeline()
    {
        return $this->readOneof(8);
    }

    public function hasSerialPipeline()
    {
        return $this->hasOneof(8);
    }

    /**
     * SerialPipeline defines a sequential set of stages for a
     * `DeliveryPipeline`.
     *
     * Generated from protobuf field <code>.google.cloud.deploy.v1.SerialPipeline serial_pipeline = 8;</code>
     * @param \Google\Cloud\Deploy\V1\SerialPipeline $var
     * @return $this
     */
    public function setSerialPipeline($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Deploy\V1\SerialPipeline::class);
        $this->writeOneof(8, $var);

        return $this;
    }

    /**
     * Output only. Information around the state of the Delivery Pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.deploy.v1.PipelineCondition condition = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\Deploy\V1\PipelineCondition|null
     */
    public function getCondition()
    {
        return $this->condition;
    }

    public function hasCondition()
    {
        return isset($this->condition);
    }

    public function clearCondition()
    {
        unset($this->condition);
    }

    /**
     * Output only. Information around the state of the Delivery Pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.deploy.v1.PipelineCondition condition = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Deploy\V1\PipelineCondition $var
     * @return $this
     */
    public function setCondition($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Deploy\V1\PipelineCondition::class);
        $this->condition = $var;

        return $this;
    }

    /**
     * This checksum is computed by the server based on the value of other
     * fields, and may be sent on update and delete requests to ensure the
     * client has an up-to-date value before proceeding.
     *
     * Generated from protobuf field <code>string etag = 10;</code>
     * @return string
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * This checksum is computed by the server based on the value of other
     * fields, and may be sent on update and delete requests to ensure the
     * client has an up-to-date value before proceeding.
     *
     * Generated from protobuf field <code>string etag = 10;</code>
     * @param string $var
     * @return $this
     */
    public function setEtag($var)
    {
        GPBUtil::checkString($var, True);
        $this->etag = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getPipeline()
    {
        return $this->whichOneof("pipeline");
    }

}

