<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/training_pipeline.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The TrainingPipeline orchestrates tasks associated with training a Model. It
 * always executes the training task, and optionally may also
 * export data from Vertex AI's Dataset which becomes the training input,
 * [upload][google.cloud.aiplatform.v1.ModelService.UploadModel] the Model to Vertex AI, and evaluate the
 * Model.
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.TrainingPipeline</code>
 */
class TrainingPipeline extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. Resource name of the TrainingPipeline.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Required. The user-defined name of this TrainingPipeline.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $display_name = '';
    /**
     * Specifies Vertex AI owned input data that may be used for training the
     * Model. The TrainingPipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make
     * clear whether this config is used and if there are any special requirements
     * on how it should be filled. If nothing about this config is mentioned in
     * the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that the
     * TrainingPipeline does not depend on this configuration.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.InputDataConfig input_data_config = 3;</code>
     */
    private $input_data_config = null;
    /**
     * Required. A Google Cloud Storage path to the YAML file that defines the training task
     * which is responsible for producing the model artifact, and may also include
     * additional auxiliary work.
     * The definition files that can be used here are found in
     * gs://google-cloud-aiplatform/schema/trainingjob/definition/.
     * Note: The URI given on output will be immutable and probably different,
     * including the URI scheme, than the one given on input. The output URI will
     * point to a location where the user only has a read access.
     *
     * Generated from protobuf field <code>string training_task_definition = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $training_task_definition = '';
    /**
     * Required. The training task's parameter(s), as specified in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s `inputs`.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_inputs = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $training_task_inputs = null;
    /**
     * Output only. The metadata information as specified in the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s
     * `metadata`. This metadata is an auxiliary runtime and final information
     * about the training task. While the pipeline is running this information is
     * populated only at a best effort basis. Only present if the
     * pipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] contains `metadata` object.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_metadata = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $training_task_metadata = null;
    /**
     * Describes the Model that may be uploaded (via [ModelService.UploadModel][google.cloud.aiplatform.v1.ModelService.UploadModel])
     * by this TrainingPipeline. The TrainingPipeline's
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make clear whether this Model
     * description should be populated, and if there are any special requirements
     * regarding how it should be filled. If nothing is mentioned in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that this field
     * should not be filled and the training task either uploads the Model without
     * a need of this information, or that training task does not support
     * uploading a Model as part of the pipeline.
     * When the Pipeline's state becomes `PIPELINE_STATE_SUCCEEDED` and
     * the trained Model had been uploaded into Vertex AI, then the
     * model_to_upload's resource [name][google.cloud.aiplatform.v1.Model.name] is populated. The Model
     * is always uploaded into the Project and Location in which this pipeline
     * is.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Model model_to_upload = 7;</code>
     */
    private $model_to_upload = null;
    /**
     * Output only. The detailed state of the pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.PipelineState state = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $state = 0;
    /**
     * Output only. Only populated when the pipeline's state is `PIPELINE_STATE_FAILED` or
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.rpc.Status error = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $error = null;
    /**
     * Output only. Time when the TrainingPipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. Time when the TrainingPipeline for the first time entered the
     * `PIPELINE_STATE_RUNNING` state.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $start_time = null;
    /**
     * Output only. Time when the TrainingPipeline entered any of the following states:
     * `PIPELINE_STATE_SUCCEEDED`, `PIPELINE_STATE_FAILED`,
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 13 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $end_time = null;
    /**
     * Output only. Time when the TrainingPipeline was most recently updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 14 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $update_time = null;
    /**
     * The labels with user-defined metadata to organize TrainingPipelines.
     * Label keys and values can be no longer than 64 characters
     * (Unicode codepoints), can only contain lowercase letters, numeric
     * characters, underscores and dashes. International characters are allowed.
     * See https://goo.gl/xmQnxf for more information and examples of labels.
     *
     * Generated from protobuf field <code>map<string, string> labels = 15;</code>
     */
    private $labels;
    /**
     * Customer-managed encryption key spec for a TrainingPipeline. If set, this
     * TrainingPipeline will be secured by this key.
     * Note: Model trained by this TrainingPipeline is also secured by this key if
     * [model_to_upload][google.cloud.aiplatform.v1.TrainingPipeline.encryption_spec] is not set separately.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.EncryptionSpec encryption_spec = 18;</code>
     */
    private $encryption_spec = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. Resource name of the TrainingPipeline.
     *     @type string $display_name
     *           Required. The user-defined name of this TrainingPipeline.
     *     @type \Google\Cloud\AIPlatform\V1\InputDataConfig $input_data_config
     *           Specifies Vertex AI owned input data that may be used for training the
     *           Model. The TrainingPipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make
     *           clear whether this config is used and if there are any special requirements
     *           on how it should be filled. If nothing about this config is mentioned in
     *           the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that the
     *           TrainingPipeline does not depend on this configuration.
     *     @type string $training_task_definition
     *           Required. A Google Cloud Storage path to the YAML file that defines the training task
     *           which is responsible for producing the model artifact, and may also include
     *           additional auxiliary work.
     *           The definition files that can be used here are found in
     *           gs://google-cloud-aiplatform/schema/trainingjob/definition/.
     *           Note: The URI given on output will be immutable and probably different,
     *           including the URI scheme, than the one given on input. The output URI will
     *           point to a location where the user only has a read access.
     *     @type \Google\Protobuf\Value $training_task_inputs
     *           Required. The training task's parameter(s), as specified in the
     *           [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s `inputs`.
     *     @type \Google\Protobuf\Value $training_task_metadata
     *           Output only. The metadata information as specified in the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s
     *           `metadata`. This metadata is an auxiliary runtime and final information
     *           about the training task. While the pipeline is running this information is
     *           populated only at a best effort basis. Only present if the
     *           pipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] contains `metadata` object.
     *     @type \Google\Cloud\AIPlatform\V1\Model $model_to_upload
     *           Describes the Model that may be uploaded (via [ModelService.UploadModel][google.cloud.aiplatform.v1.ModelService.UploadModel])
     *           by this TrainingPipeline. The TrainingPipeline's
     *           [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make clear whether this Model
     *           description should be populated, and if there are any special requirements
     *           regarding how it should be filled. If nothing is mentioned in the
     *           [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that this field
     *           should not be filled and the training task either uploads the Model without
     *           a need of this information, or that training task does not support
     *           uploading a Model as part of the pipeline.
     *           When the Pipeline's state becomes `PIPELINE_STATE_SUCCEEDED` and
     *           the trained Model had been uploaded into Vertex AI, then the
     *           model_to_upload's resource [name][google.cloud.aiplatform.v1.Model.name] is populated. The Model
     *           is always uploaded into the Project and Location in which this pipeline
     *           is.
     *     @type int $state
     *           Output only. The detailed state of the pipeline.
     *     @type \Google\Rpc\Status $error
     *           Output only. Only populated when the pipeline's state is `PIPELINE_STATE_FAILED` or
     *           `PIPELINE_STATE_CANCELLED`.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. Time when the TrainingPipeline was created.
     *     @type \Google\Protobuf\Timestamp $start_time
     *           Output only. Time when the TrainingPipeline for the first time entered the
     *           `PIPELINE_STATE_RUNNING` state.
     *     @type \Google\Protobuf\Timestamp $end_time
     *           Output only. Time when the TrainingPipeline entered any of the following states:
     *           `PIPELINE_STATE_SUCCEEDED`, `PIPELINE_STATE_FAILED`,
     *           `PIPELINE_STATE_CANCELLED`.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Output only. Time when the TrainingPipeline was most recently updated.
     *     @type array|\Google\Protobuf\Internal\MapField $labels
     *           The labels with user-defined metadata to organize TrainingPipelines.
     *           Label keys and values can be no longer than 64 characters
     *           (Unicode codepoints), can only contain lowercase letters, numeric
     *           characters, underscores and dashes. International characters are allowed.
     *           See https://goo.gl/xmQnxf for more information and examples of labels.
     *     @type \Google\Cloud\AIPlatform\V1\EncryptionSpec $encryption_spec
     *           Customer-managed encryption key spec for a TrainingPipeline. If set, this
     *           TrainingPipeline will be secured by this key.
     *           Note: Model trained by this TrainingPipeline is also secured by this key if
     *           [model_to_upload][google.cloud.aiplatform.v1.TrainingPipeline.encryption_spec] is not set separately.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\TrainingPipeline::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. Resource name of the TrainingPipeline.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. Resource name of the TrainingPipeline.
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
     * Required. The user-defined name of this TrainingPipeline.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Required. The user-defined name of this TrainingPipeline.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * Specifies Vertex AI owned input data that may be used for training the
     * Model. The TrainingPipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make
     * clear whether this config is used and if there are any special requirements
     * on how it should be filled. If nothing about this config is mentioned in
     * the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that the
     * TrainingPipeline does not depend on this configuration.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.InputDataConfig input_data_config = 3;</code>
     * @return \Google\Cloud\AIPlatform\V1\InputDataConfig|null
     */
    public function getInputDataConfig()
    {
        return $this->input_data_config;
    }

    public function hasInputDataConfig()
    {
        return isset($this->input_data_config);
    }

    public function clearInputDataConfig()
    {
        unset($this->input_data_config);
    }

    /**
     * Specifies Vertex AI owned input data that may be used for training the
     * Model. The TrainingPipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make
     * clear whether this config is used and if there are any special requirements
     * on how it should be filled. If nothing about this config is mentioned in
     * the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that the
     * TrainingPipeline does not depend on this configuration.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.InputDataConfig input_data_config = 3;</code>
     * @param \Google\Cloud\AIPlatform\V1\InputDataConfig $var
     * @return $this
     */
    public function setInputDataConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\InputDataConfig::class);
        $this->input_data_config = $var;

        return $this;
    }

    /**
     * Required. A Google Cloud Storage path to the YAML file that defines the training task
     * which is responsible for producing the model artifact, and may also include
     * additional auxiliary work.
     * The definition files that can be used here are found in
     * gs://google-cloud-aiplatform/schema/trainingjob/definition/.
     * Note: The URI given on output will be immutable and probably different,
     * including the URI scheme, than the one given on input. The output URI will
     * point to a location where the user only has a read access.
     *
     * Generated from protobuf field <code>string training_task_definition = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getTrainingTaskDefinition()
    {
        return $this->training_task_definition;
    }

    /**
     * Required. A Google Cloud Storage path to the YAML file that defines the training task
     * which is responsible for producing the model artifact, and may also include
     * additional auxiliary work.
     * The definition files that can be used here are found in
     * gs://google-cloud-aiplatform/schema/trainingjob/definition/.
     * Note: The URI given on output will be immutable and probably different,
     * including the URI scheme, than the one given on input. The output URI will
     * point to a location where the user only has a read access.
     *
     * Generated from protobuf field <code>string training_task_definition = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setTrainingTaskDefinition($var)
    {
        GPBUtil::checkString($var, True);
        $this->training_task_definition = $var;

        return $this;
    }

    /**
     * Required. The training task's parameter(s), as specified in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s `inputs`.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_inputs = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Value|null
     */
    public function getTrainingTaskInputs()
    {
        return $this->training_task_inputs;
    }

    public function hasTrainingTaskInputs()
    {
        return isset($this->training_task_inputs);
    }

    public function clearTrainingTaskInputs()
    {
        unset($this->training_task_inputs);
    }

    /**
     * Required. The training task's parameter(s), as specified in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s `inputs`.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_inputs = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Protobuf\Value $var
     * @return $this
     */
    public function setTrainingTaskInputs($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Value::class);
        $this->training_task_inputs = $var;

        return $this;
    }

    /**
     * Output only. The metadata information as specified in the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s
     * `metadata`. This metadata is an auxiliary runtime and final information
     * about the training task. While the pipeline is running this information is
     * populated only at a best effort basis. Only present if the
     * pipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] contains `metadata` object.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_metadata = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Value|null
     */
    public function getTrainingTaskMetadata()
    {
        return $this->training_task_metadata;
    }

    public function hasTrainingTaskMetadata()
    {
        return isset($this->training_task_metadata);
    }

    public function clearTrainingTaskMetadata()
    {
        unset($this->training_task_metadata);
    }

    /**
     * Output only. The metadata information as specified in the [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition]'s
     * `metadata`. This metadata is an auxiliary runtime and final information
     * about the training task. While the pipeline is running this information is
     * populated only at a best effort basis. Only present if the
     * pipeline's [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] contains `metadata` object.
     *
     * Generated from protobuf field <code>.google.protobuf.Value training_task_metadata = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Value $var
     * @return $this
     */
    public function setTrainingTaskMetadata($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Value::class);
        $this->training_task_metadata = $var;

        return $this;
    }

    /**
     * Describes the Model that may be uploaded (via [ModelService.UploadModel][google.cloud.aiplatform.v1.ModelService.UploadModel])
     * by this TrainingPipeline. The TrainingPipeline's
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make clear whether this Model
     * description should be populated, and if there are any special requirements
     * regarding how it should be filled. If nothing is mentioned in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that this field
     * should not be filled and the training task either uploads the Model without
     * a need of this information, or that training task does not support
     * uploading a Model as part of the pipeline.
     * When the Pipeline's state becomes `PIPELINE_STATE_SUCCEEDED` and
     * the trained Model had been uploaded into Vertex AI, then the
     * model_to_upload's resource [name][google.cloud.aiplatform.v1.Model.name] is populated. The Model
     * is always uploaded into the Project and Location in which this pipeline
     * is.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Model model_to_upload = 7;</code>
     * @return \Google\Cloud\AIPlatform\V1\Model|null
     */
    public function getModelToUpload()
    {
        return $this->model_to_upload;
    }

    public function hasModelToUpload()
    {
        return isset($this->model_to_upload);
    }

    public function clearModelToUpload()
    {
        unset($this->model_to_upload);
    }

    /**
     * Describes the Model that may be uploaded (via [ModelService.UploadModel][google.cloud.aiplatform.v1.ModelService.UploadModel])
     * by this TrainingPipeline. The TrainingPipeline's
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition] should make clear whether this Model
     * description should be populated, and if there are any special requirements
     * regarding how it should be filled. If nothing is mentioned in the
     * [training_task_definition][google.cloud.aiplatform.v1.TrainingPipeline.training_task_definition], then it should be assumed that this field
     * should not be filled and the training task either uploads the Model without
     * a need of this information, or that training task does not support
     * uploading a Model as part of the pipeline.
     * When the Pipeline's state becomes `PIPELINE_STATE_SUCCEEDED` and
     * the trained Model had been uploaded into Vertex AI, then the
     * model_to_upload's resource [name][google.cloud.aiplatform.v1.Model.name] is populated. The Model
     * is always uploaded into the Project and Location in which this pipeline
     * is.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.Model model_to_upload = 7;</code>
     * @param \Google\Cloud\AIPlatform\V1\Model $var
     * @return $this
     */
    public function setModelToUpload($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\Model::class);
        $this->model_to_upload = $var;

        return $this;
    }

    /**
     * Output only. The detailed state of the pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.PipelineState state = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Output only. The detailed state of the pipeline.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.PipelineState state = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setState($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\AIPlatform\V1\PipelineState::class);
        $this->state = $var;

        return $this;
    }

    /**
     * Output only. Only populated when the pipeline's state is `PIPELINE_STATE_FAILED` or
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.rpc.Status error = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Rpc\Status|null
     */
    public function getError()
    {
        return $this->error;
    }

    public function hasError()
    {
        return isset($this->error);
    }

    public function clearError()
    {
        unset($this->error);
    }

    /**
     * Output only. Only populated when the pipeline's state is `PIPELINE_STATE_FAILED` or
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.rpc.Status error = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Rpc\Status $var
     * @return $this
     */
    public function setError($var)
    {
        GPBUtil::checkMessage($var, \Google\Rpc\Status::class);
        $this->error = $var;

        return $this;
    }

    /**
     * Output only. Time when the TrainingPipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. Time when the TrainingPipeline was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 11 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. Time when the TrainingPipeline for the first time entered the
     * `PIPELINE_STATE_RUNNING` state.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    public function hasStartTime()
    {
        return isset($this->start_time);
    }

    public function clearStartTime()
    {
        unset($this->start_time);
    }

    /**
     * Output only. Time when the TrainingPipeline for the first time entered the
     * `PIPELINE_STATE_RUNNING` state.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp start_time = 12 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setStartTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->start_time = $var;

        return $this;
    }

    /**
     * Output only. Time when the TrainingPipeline entered any of the following states:
     * `PIPELINE_STATE_SUCCEEDED`, `PIPELINE_STATE_FAILED`,
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 13 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    public function hasEndTime()
    {
        return isset($this->end_time);
    }

    public function clearEndTime()
    {
        unset($this->end_time);
    }

    /**
     * Output only. Time when the TrainingPipeline entered any of the following states:
     * `PIPELINE_STATE_SUCCEEDED`, `PIPELINE_STATE_FAILED`,
     * `PIPELINE_STATE_CANCELLED`.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp end_time = 13 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setEndTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->end_time = $var;

        return $this;
    }

    /**
     * Output only. Time when the TrainingPipeline was most recently updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 14 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. Time when the TrainingPipeline was most recently updated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 14 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * The labels with user-defined metadata to organize TrainingPipelines.
     * Label keys and values can be no longer than 64 characters
     * (Unicode codepoints), can only contain lowercase letters, numeric
     * characters, underscores and dashes. International characters are allowed.
     * See https://goo.gl/xmQnxf for more information and examples of labels.
     *
     * Generated from protobuf field <code>map<string, string> labels = 15;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * The labels with user-defined metadata to organize TrainingPipelines.
     * Label keys and values can be no longer than 64 characters
     * (Unicode codepoints), can only contain lowercase letters, numeric
     * characters, underscores and dashes. International characters are allowed.
     * See https://goo.gl/xmQnxf for more information and examples of labels.
     *
     * Generated from protobuf field <code>map<string, string> labels = 15;</code>
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
     * Customer-managed encryption key spec for a TrainingPipeline. If set, this
     * TrainingPipeline will be secured by this key.
     * Note: Model trained by this TrainingPipeline is also secured by this key if
     * [model_to_upload][google.cloud.aiplatform.v1.TrainingPipeline.encryption_spec] is not set separately.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.EncryptionSpec encryption_spec = 18;</code>
     * @return \Google\Cloud\AIPlatform\V1\EncryptionSpec|null
     */
    public function getEncryptionSpec()
    {
        return $this->encryption_spec;
    }

    public function hasEncryptionSpec()
    {
        return isset($this->encryption_spec);
    }

    public function clearEncryptionSpec()
    {
        unset($this->encryption_spec);
    }

    /**
     * Customer-managed encryption key spec for a TrainingPipeline. If set, this
     * TrainingPipeline will be secured by this key.
     * Note: Model trained by this TrainingPipeline is also secured by this key if
     * [model_to_upload][google.cloud.aiplatform.v1.TrainingPipeline.encryption_spec] is not set separately.
     *
     * Generated from protobuf field <code>.google.cloud.aiplatform.v1.EncryptionSpec encryption_spec = 18;</code>
     * @param \Google\Cloud\AIPlatform\V1\EncryptionSpec $var
     * @return $this
     */
    public function setEncryptionSpec($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AIPlatform\V1\EncryptionSpec::class);
        $this->encryption_spec = $var;

        return $this;
    }

}

