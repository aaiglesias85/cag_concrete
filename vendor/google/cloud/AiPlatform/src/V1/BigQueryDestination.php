<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/aiplatform/v1/io.proto

namespace Google\Cloud\AIPlatform\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The BigQuery location for the output content.
 *
 * Generated from protobuf message <code>google.cloud.aiplatform.v1.BigQueryDestination</code>
 */
class BigQueryDestination extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. BigQuery URI to a project or table, up to 2000 characters long.
     * When only the project is specified, the Dataset and Table is created.
     * When the full table reference is specified, the Dataset must exist and
     * table must not exist.
     * Accepted forms:
     * *  BigQuery path. For example:
     * `bq://projectId` or `bq://projectId.bqDatasetId` or
     * `bq://projectId.bqDatasetId.bqTableId`.
     *
     * Generated from protobuf field <code>string output_uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $output_uri = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $output_uri
     *           Required. BigQuery URI to a project or table, up to 2000 characters long.
     *           When only the project is specified, the Dataset and Table is created.
     *           When the full table reference is specified, the Dataset must exist and
     *           table must not exist.
     *           Accepted forms:
     *           *  BigQuery path. For example:
     *           `bq://projectId` or `bq://projectId.bqDatasetId` or
     *           `bq://projectId.bqDatasetId.bqTableId`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Aiplatform\V1\Io::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. BigQuery URI to a project or table, up to 2000 characters long.
     * When only the project is specified, the Dataset and Table is created.
     * When the full table reference is specified, the Dataset must exist and
     * table must not exist.
     * Accepted forms:
     * *  BigQuery path. For example:
     * `bq://projectId` or `bq://projectId.bqDatasetId` or
     * `bq://projectId.bqDatasetId.bqTableId`.
     *
     * Generated from protobuf field <code>string output_uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getOutputUri()
    {
        return $this->output_uri;
    }

    /**
     * Required. BigQuery URI to a project or table, up to 2000 characters long.
     * When only the project is specified, the Dataset and Table is created.
     * When the full table reference is specified, the Dataset must exist and
     * table must not exist.
     * Accepted forms:
     * *  BigQuery path. For example:
     * `bq://projectId` or `bq://projectId.bqDatasetId` or
     * `bq://projectId.bqDatasetId.bqTableId`.
     *
     * Generated from protobuf field <code>string output_uri = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setOutputUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->output_uri = $var;

        return $this;
    }

}

