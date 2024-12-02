<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/bigquery/connection/v1/connection.proto

namespace Google\Cloud\BigQuery\Connection\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Connection properties specific to the Cloud SQL.
 *
 * Generated from protobuf message <code>google.cloud.bigquery.connection.v1.CloudSqlProperties</code>
 */
class CloudSqlProperties extends \Google\Protobuf\Internal\Message
{
    /**
     * Cloud SQL instance ID in the form `project:location:instance`.
     *
     * Generated from protobuf field <code>string instance_id = 1;</code>
     */
    private $instance_id = '';
    /**
     * Database name.
     *
     * Generated from protobuf field <code>string database = 2;</code>
     */
    private $database = '';
    /**
     * Type of the Cloud SQL database.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlProperties.DatabaseType type = 3;</code>
     */
    private $type = 0;
    /**
     * Input only. Cloud SQL credential.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlCredential credential = 4 [(.google.api.field_behavior) = INPUT_ONLY];</code>
     */
    private $credential = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $instance_id
     *           Cloud SQL instance ID in the form `project:location:instance`.
     *     @type string $database
     *           Database name.
     *     @type int $type
     *           Type of the Cloud SQL database.
     *     @type \Google\Cloud\BigQuery\Connection\V1\CloudSqlCredential $credential
     *           Input only. Cloud SQL credential.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Bigquery\Connection\V1\Connection::initOnce();
        parent::__construct($data);
    }

    /**
     * Cloud SQL instance ID in the form `project:location:instance`.
     *
     * Generated from protobuf field <code>string instance_id = 1;</code>
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instance_id;
    }

    /**
     * Cloud SQL instance ID in the form `project:location:instance`.
     *
     * Generated from protobuf field <code>string instance_id = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setInstanceId($var)
    {
        GPBUtil::checkString($var, True);
        $this->instance_id = $var;

        return $this;
    }

    /**
     * Database name.
     *
     * Generated from protobuf field <code>string database = 2;</code>
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Database name.
     *
     * Generated from protobuf field <code>string database = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setDatabase($var)
    {
        GPBUtil::checkString($var, True);
        $this->database = $var;

        return $this;
    }

    /**
     * Type of the Cloud SQL database.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlProperties.DatabaseType type = 3;</code>
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type of the Cloud SQL database.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlProperties.DatabaseType type = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\BigQuery\Connection\V1\CloudSqlProperties\DatabaseType::class);
        $this->type = $var;

        return $this;
    }

    /**
     * Input only. Cloud SQL credential.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlCredential credential = 4 [(.google.api.field_behavior) = INPUT_ONLY];</code>
     * @return \Google\Cloud\BigQuery\Connection\V1\CloudSqlCredential|null
     */
    public function getCredential()
    {
        return $this->credential;
    }

    public function hasCredential()
    {
        return isset($this->credential);
    }

    public function clearCredential()
    {
        unset($this->credential);
    }

    /**
     * Input only. Cloud SQL credential.
     *
     * Generated from protobuf field <code>.google.cloud.bigquery.connection.v1.CloudSqlCredential credential = 4 [(.google.api.field_behavior) = INPUT_ONLY];</code>
     * @param \Google\Cloud\BigQuery\Connection\V1\CloudSqlCredential $var
     * @return $this
     */
    public function setCredential($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\BigQuery\Connection\V1\CloudSqlCredential::class);
        $this->credential = $var;

        return $this;
    }

}

