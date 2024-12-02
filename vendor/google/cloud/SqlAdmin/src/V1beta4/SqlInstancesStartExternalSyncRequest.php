<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/sql/v1beta4/cloud_sql.proto

namespace Google\Cloud\Sql\V1beta4;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>google.cloud.sql.v1beta4.SqlInstancesStartExternalSyncRequest</code>
 */
class SqlInstancesStartExternalSyncRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     */
    private $instance = '';
    /**
     * ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
     */
    private $project = '';
    /**
     * External sync mode.
     *
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.SqlInstancesVerifyExternalSyncSettingsRequest.ExternalSyncMode sync_mode = 3;</code>
     */
    private $sync_mode = 0;
    /**
     * Whether to skip the verification step (VESS).
     *
     * Generated from protobuf field <code>bool skip_verification = 4;</code>
     */
    private $skip_verification = false;
    protected $sync_config;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $instance
     *           Cloud SQL instance ID. This does not include the project ID.
     *     @type string $project
     *           ID of the project that contains the instance.
     *     @type int $sync_mode
     *           External sync mode.
     *     @type bool $skip_verification
     *           Whether to skip the verification step (VESS).
     *     @type \Google\Cloud\Sql\V1beta4\MySqlSyncConfig $mysql_sync_config
     *           MySQL-specific settings for start external sync.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Sql\V1Beta4\CloudSql::initOnce();
        parent::__construct($data);
    }

    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Cloud SQL instance ID. This does not include the project ID.
     *
     * Generated from protobuf field <code>string instance = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setInstance($var)
    {
        GPBUtil::checkString($var, True);
        $this->instance = $var;

        return $this;
    }

    /**
     * ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * ID of the project that contains the instance.
     *
     * Generated from protobuf field <code>string project = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setProject($var)
    {
        GPBUtil::checkString($var, True);
        $this->project = $var;

        return $this;
    }

    /**
     * External sync mode.
     *
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.SqlInstancesVerifyExternalSyncSettingsRequest.ExternalSyncMode sync_mode = 3;</code>
     * @return int
     */
    public function getSyncMode()
    {
        return $this->sync_mode;
    }

    /**
     * External sync mode.
     *
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.SqlInstancesVerifyExternalSyncSettingsRequest.ExternalSyncMode sync_mode = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setSyncMode($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Sql\V1beta4\SqlInstancesVerifyExternalSyncSettingsRequest\ExternalSyncMode::class);
        $this->sync_mode = $var;

        return $this;
    }

    /**
     * Whether to skip the verification step (VESS).
     *
     * Generated from protobuf field <code>bool skip_verification = 4;</code>
     * @return bool
     */
    public function getSkipVerification()
    {
        return $this->skip_verification;
    }

    /**
     * Whether to skip the verification step (VESS).
     *
     * Generated from protobuf field <code>bool skip_verification = 4;</code>
     * @param bool $var
     * @return $this
     */
    public function setSkipVerification($var)
    {
        GPBUtil::checkBool($var);
        $this->skip_verification = $var;

        return $this;
    }

    /**
     * MySQL-specific settings for start external sync.
     *
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.MySqlSyncConfig mysql_sync_config = 6;</code>
     * @return \Google\Cloud\Sql\V1beta4\MySqlSyncConfig|null
     */
    public function getMysqlSyncConfig()
    {
        return $this->readOneof(6);
    }

    public function hasMysqlSyncConfig()
    {
        return $this->hasOneof(6);
    }

    /**
     * MySQL-specific settings for start external sync.
     *
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.MySqlSyncConfig mysql_sync_config = 6;</code>
     * @param \Google\Cloud\Sql\V1beta4\MySqlSyncConfig $var
     * @return $this
     */
    public function setMysqlSyncConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Sql\V1beta4\MySqlSyncConfig::class);
        $this->writeOneof(6, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getSyncConfig()
    {
        return $this->whichOneof("sync_config");
    }

}

