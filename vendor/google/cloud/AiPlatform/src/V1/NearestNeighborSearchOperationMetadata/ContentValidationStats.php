<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/index_service.proto

namespace Google\Cloud\AIPlatform\V1\NearestNeighborSearchOperationMetadata;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.NearestNeighborSearchOperationMetadata.ContentValidationStats</code>
 */
class ContentValidationStats extends \Google\Protobuf\Internal\Message
{
    /**
     * Cloud Storage URI pointing to the original file in user's bucket.
     *
     * Generated from protobuf field <code>string source_gcs_uri = 1;</code>
     */
    private $source_gcs_uri = '';
    /**
     * Number of records in this file that were successfully processed.
     *
     * Generated from protobuf field <code>int64 valid_record_count = 2;</code>
     */
    private $valid_record_count = 0;
    /**
     * Number of records in this file we skipped due to validate errors.
     *
     * Generated from protobuf field <code>int64 invalid_record_count = 3;</code>
     */
    private $invalid_record_count = 0;
    /**
     * The detail information of the partial failures encountered for those
     * invalid records that couldn't be parsed.
     * Up to 50 partial errors will be reported.
     *
     * Generated from protobuf field <code>repeated .google.cloud.aiplatform.v1.NearestNeighborSearchOperationMetadata.RecordError partial_errors = 4;</code>
     */
    private $partial_errors;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $source_gcs_uri
     *           Cloud Storage URI pointing to the original file in user's bucket.
     *     @type int|string $valid_record_count
     *           Number of records in this file that were successfully processed.
     *     @type int|string $invalid_record_count
     *           Number of records in this file we skipped due to validate errors.
     *     @type \Google\Cloud\AIPlatform\V1\NearestNeighborSearchOperationMetadata\RecordError[]|\Google\Protobuf\Internal\RepeatedField $partial_errors
     *           The detail information of the partial failures encountered for those
     *           invalid records that couldn't be parsed.
     *           Up to 50 partial errors will be reported.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\IndexService::initOnce();
        parent::__construct($data);
    }

    /**
     * Cloud Storage URI pointing to the original file in user's bucket.
     *
     * Generated from protobuf field <code>string source_gcs_uri = 1;</code>
     * @return string
     */
    public function getSourceGcsUri()
    {
        return $this->source_gcs_uri;
    }

    /**
     * Cloud Storage URI pointing to the original file in user's bucket.
     *
     * Generated from protobuf field <code>string source_gcs_uri = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSourceGcsUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->source_gcs_uri = $var;

        return $this;
    }

    /**
     * Number of records in this file that were successfully processed.
     *
     * Generated from protobuf field <code>int64 valid_record_count = 2;</code>
     * @return int|string
     */
    public function getValidRecordCount()
    {
        return $this->valid_record_count;
    }

    /**
     * Number of records in this file that were successfully processed.
     *
     * Generated from protobuf field <code>int64 valid_record_count = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setValidRecordCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->valid_record_count = $var;

        return $this;
    }

    /**
     * Number of records in this file we skipped due to validate errors.
     *
     * Generated from protobuf field <code>int64 invalid_record_count = 3;</code>
     * @return int|string
     */
    public function getInvalidRecordCount()
    {
        return $this->invalid_record_count;
    }

    /**
     * Number of records in this file we skipped due to validate errors.
     *
     * Generated from protobuf field <code>int64 invalid_record_count = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setInvalidRecordCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->invalid_record_count = $var;

        return $this;
    }

    /**
     * The detail information of the partial failures encountered for those
     * invalid records that couldn't be parsed.
     * Up to 50 partial errors will be reported.
     *
     * Generated from protobuf field <code>repeated .google.cloud.aiplatform.v1.NearestNeighborSearchOperationMetadata.RecordError partial_errors = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPartialErrors()
    {
        return $this->partial_errors;
    }

    /**
     * The detail information of the partial failures encountered for those
     * invalid records that couldn't be parsed.
     * Up to 50 partial errors will be reported.
     *
     * Generated from protobuf field <code>repeated .google.cloud.aiplatform.v1.NearestNeighborSearchOperationMetadata.RecordError partial_errors = 4;</code>
     * @param \Google\Cloud\AIPlatform\V1\NearestNeighborSearchOperationMetadata\RecordError[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPartialErrors($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\AIPlatform\V1\NearestNeighborSearchOperationMetadata\RecordError::class);
        $this->partial_errors = $arr;

        return $this;
    }

}


