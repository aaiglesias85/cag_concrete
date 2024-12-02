<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/model_evaluation_slice.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A collection of metrics calculated by comparing Model's predictions on a
 * slice of the test data against ground truth annotations.
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.ModelEvaluationSlice</code>
 */
class ModelEvaluationSlice extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. The resource name of the ModelEvaluationSlice.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Output only. The slice of the test data that is used to evaluate the Model.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.ModelEvaluationSlice.Slice slice = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $slice = null;
    /**
     * Output only. Points to a YAML file stored on Google Cloud Storage describing the
     * [metrics][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics] of this ModelEvaluationSlice. The
     * schema is defined as an OpenAPI 3.0.2 [Schema
     * Object](https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.2.md#schemaObject).
     *
     * Generated from protobuf field <code>string metrics_schema_uri = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $metrics_schema_uri = '';
    /**
     * Output only. Sliced evaluation metrics of the Model. The schema of the metrics is stored
     * in [metrics_schema_uri][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics_schema_uri]
     *
     * Generated from protobuf field <code>.google.protobuf.Value metrics = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $metrics = null;
    /**
     * Output only. Timestamp when this ModelEvaluationSlice was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. The resource name of the ModelEvaluationSlice.
     *     @type \Google\Cloud\AIPlatform\V1\ModelEvaluationSlice\Slice $slice
     *           Output only. The slice of the test data that is used to evaluate the Model.
     *     @type string $metrics_schema_uri
     *           Output only. Points to a YAML file stored on Google Cloud Storage describing the
     *           [metrics][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics] of this ModelEvaluationSlice. The
     *           schema is defined as an OpenAPI 3.0.2 [Schema
     *           Object](https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.2.md#schemaObject).
     *     @type \Google\Protobuf\Value $metrics
     *           Output only. Sliced evaluation metrics of the Model. The schema of the metrics is stored
     *           in [metrics_schema_uri][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics_schema_uri]
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Timestamp when this ModelEvaluationSlice was created.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\ModelEvaluationSlice::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. The resource name of the ModelEvaluationSlice.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. The resource name of the ModelEvaluationSlice.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. The slice of the test data that is used to evaluate the Model.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.ModelEvaluationSlice.Slice slice = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\AIPlatform\V1\ModelEvaluationSlice\Slice|null
     */
    public function getSlice()
    {
        return $this->slice;
    }

    public function hasSlice()
    {
        return isset($this->slice);
    }

    public function clearSlice()
    {
        unset($this->slice);
    }

    /**
     * Output only. The slice of the test data that is used to evaluate the Model.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.ModelEvaluationSlice.Slice slice = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\AIPlatform\V1\ModelEvaluationSlice\Slice $var
     * @return $this
     */
    public function setSlice($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\ModelEvaluationSlice\Slice::class);
        $this->slice = $var;

        return $this;
    }

    /**
     * Output only. Points to a YAML file stored on Google Cloud Storage describing the
     * [metrics][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics] of this ModelEvaluationSlice. The
     * schema is defined as an OpenAPI 3.0.2 [Schema
     * Object](https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.2.md#schemaObject).
     *
     * Generated from protobuf field <code>string metrics_schema_uri = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getMetricsSchemaUri()
    {
        return $this->metrics_schema_uri;
    }

    /**
     * Output only. Points to a YAML file stored on Google Cloud Storage describing the
     * [metrics][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics] of this ModelEvaluationSlice. The
     * schema is defined as an OpenAPI 3.0.2 [Schema
     * Object](https://github.com/OAI/OpenAPI-Specification/blob/main/versions/3.0.2.md#schemaObject).
     *
     * Generated from protobuf field <code>string metrics_schema_uri = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setMetricsSchemaUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->metrics_schema_uri = $var;

        return $this;
    }

    /**
     * Output only. Sliced evaluation metrics of the Model. The schema of the metrics is stored
     * in [metrics_schema_uri][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics_schema_uri]
     *
     * Generated from protobuf field <code>.google.protobuf.Value metrics = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Value|null
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    public function hasMetrics()
    {
        return isset($this->metrics);
    }

    public function clearMetrics()
    {
        unset($this->metrics);
    }

    /**
     * Output only. Sliced evaluation metrics of the Model. The schema of the metrics is stored
     * in [metrics_schema_uri][google.cloud.aiplatform.v1.ModelEvaluationSlice.metrics_schema_uri]
     *
     * Generated from protobuf field <code>.google.protobuf.Value metrics = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Value $var
     * @return $this
     */
    public function setMetrics($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Value::class);
        $this->metrics = $var;

        return $this;
    }

    /**
     * Output only. Timestamp when this ModelEvaluationSlice was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. Timestamp when this ModelEvaluationSlice was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

}

