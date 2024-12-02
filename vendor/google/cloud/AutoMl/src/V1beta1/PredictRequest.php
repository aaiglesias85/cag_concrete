<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/automl/v1beta1/prediction_service.proto

namespace Google\Cloud\AutoMl\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for [PredictionService.Predict][google.cloud.automl.v1beta1.PredictionService.Predict].
 *
 * Generated from protobuf message <code>google.cloud.automl.v1beta1.PredictRequest</code>
 */
class PredictRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Name of the model requested to serve the prediction.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $name = '';
    /**
     * Required. Payload to perform a prediction on. The payload must match the
     * problem type that the model was trained to solve.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1beta1.ExamplePayload payload = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $payload = null;
    /**
     * Additional domain-specific parameters, any string must be up to 25000
     * characters long.
     * *  For Image Classification:
     *    `score_threshold` - (float) A value from 0.0 to 1.0. When the model
     *     makes predictions for an image, it will only produce results that have
     *     at least this confidence score. The default is 0.5.
     *  *  For Image Object Detection:
     *    `score_threshold` - (float) When Model detects objects on the image,
     *        it will only produce bounding boxes which have at least this
     *        confidence score. Value in 0 to 1 range, default is 0.5.
     *    `max_bounding_box_count` - (int64) No more than this number of bounding
     *        boxes will be returned in the response. Default is 100, the
     *        requested value may be limited by server.
     * *  For Tables:
     *    feature_imp<span>ortan</span>ce - (boolean) Whether feature importance
     *        should be populated in the returned TablesAnnotation.
     *        The default is false.
     *
     * Generated from protobuf field <code>map<string, string> params = 3;</code>
     */
    private $params;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Required. Name of the model requested to serve the prediction.
     *     @type \Google\Cloud\AutoMl\V1beta1\ExamplePayload $payload
     *           Required. Payload to perform a prediction on. The payload must match the
     *           problem type that the model was trained to solve.
     *     @type array|\Google\Protobuf\Internal\MapField $params
     *           Additional domain-specific parameters, any string must be up to 25000
     *           characters long.
     *           *  For Image Classification:
     *              `score_threshold` - (float) A value from 0.0 to 1.0. When the model
     *               makes predictions for an image, it will only produce results that have
     *               at least this confidence score. The default is 0.5.
     *            *  For Image Object Detection:
     *              `score_threshold` - (float) When Model detects objects on the image,
     *                  it will only produce bounding boxes which have at least this
     *                  confidence score. Value in 0 to 1 range, default is 0.5.
     *              `max_bounding_box_count` - (int64) No more than this number of bounding
     *                  boxes will be returned in the response. Default is 100, the
     *                  requested value may be limited by server.
     *           *  For Tables:
     *              feature_imp<span>ortan</span>ce - (boolean) Whether feature importance
     *                  should be populated in the returned TablesAnnotation.
     *                  The default is false.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Automl\V1Beta1\PredictionService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Name of the model requested to serve the prediction.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Required. Name of the model requested to serve the prediction.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
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
     * Required. Payload to perform a prediction on. The payload must match the
     * problem type that the model was trained to solve.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1beta1.ExamplePayload payload = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\AutoMl\V1beta1\ExamplePayload|null
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function hasPayload()
    {
        return isset($this->payload);
    }

    public function clearPayload()
    {
        unset($this->payload);
    }

    /**
     * Required. Payload to perform a prediction on. The payload must match the
     * problem type that the model was trained to solve.
     *
     * Generated from protobuf field <code>.google.cloud.automl.v1beta1.ExamplePayload payload = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\AutoMl\V1beta1\ExamplePayload $var
     * @return $this
     */
    public function setPayload($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AutoMl\V1beta1\ExamplePayload::class);
        $this->payload = $var;

        return $this;
    }

    /**
     * Additional domain-specific parameters, any string must be up to 25000
     * characters long.
     * *  For Image Classification:
     *    `score_threshold` - (float) A value from 0.0 to 1.0. When the model
     *     makes predictions for an image, it will only produce results that have
     *     at least this confidence score. The default is 0.5.
     *  *  For Image Object Detection:
     *    `score_threshold` - (float) When Model detects objects on the image,
     *        it will only produce bounding boxes which have at least this
     *        confidence score. Value in 0 to 1 range, default is 0.5.
     *    `max_bounding_box_count` - (int64) No more than this number of bounding
     *        boxes will be returned in the response. Default is 100, the
     *        requested value may be limited by server.
     * *  For Tables:
     *    feature_imp<span>ortan</span>ce - (boolean) Whether feature importance
     *        should be populated in the returned TablesAnnotation.
     *        The default is false.
     *
     * Generated from protobuf field <code>map<string, string> params = 3;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Additional domain-specific parameters, any string must be up to 25000
     * characters long.
     * *  For Image Classification:
     *    `score_threshold` - (float) A value from 0.0 to 1.0. When the model
     *     makes predictions for an image, it will only produce results that have
     *     at least this confidence score. The default is 0.5.
     *  *  For Image Object Detection:
     *    `score_threshold` - (float) When Model detects objects on the image,
     *        it will only produce bounding boxes which have at least this
     *        confidence score. Value in 0 to 1 range, default is 0.5.
     *    `max_bounding_box_count` - (int64) No more than this number of bounding
     *        boxes will be returned in the response. Default is 100, the
     *        requested value may be limited by server.
     * *  For Tables:
     *    feature_imp<span>ortan</span>ce - (boolean) Whether feature importance
     *        should be populated in the returned TablesAnnotation.
     *        The default is false.
     *
     * Generated from protobuf field <code>map<string, string> params = 3;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setParams($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->params = $arr;

        return $this;
    }

}

