<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/sql/v1beta4/cloud_sql_resources.proto

namespace Google\Cloud\Sql\V1beta4;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * MySQL-specific external server sync settings.
 *
 * Generated from protobuf message <code>google.cloud.sql.v1beta4.MySqlSyncConfig</code>
 */
class MySqlSyncConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * Flags to use for the initial dump.
     *
     * Generated from protobuf field <code>repeated .google.cloud.sql.v1beta4.SyncFlags initial_sync_flags = 1;</code>
     */
    private $initial_sync_flags;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Sql\V1beta4\SyncFlags[]|\Google\Protobuf\Internal\RepeatedField $initial_sync_flags
     *           Flags to use for the initial dump.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Sql\V1Beta4\CloudSqlResources::initOnce();
        parent::__construct($data);
    }

    /**
     * Flags to use for the initial dump.
     *
     * Generated from protobuf field <code>repeated .google.cloud.sql.v1beta4.SyncFlags initial_sync_flags = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInitialSyncFlags()
    {
        return $this->initial_sync_flags;
    }

    /**
     * Flags to use for the initial dump.
     *
     * Generated from protobuf field <code>repeated .google.cloud.sql.v1beta4.SyncFlags initial_sync_flags = 1;</code>
     * @param \Google\Cloud\Sql\V1beta4\SyncFlags[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInitialSyncFlags($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Sql\V1beta4\SyncFlags::class);
        $this->initial_sync_flags = $arr;

        return $this;
    }

}

