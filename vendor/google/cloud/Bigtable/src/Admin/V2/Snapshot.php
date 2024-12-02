<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/bigtable/admin/v2/table.proto

namespace Google\Cloud\Bigtable\Admin\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A snapshot of a table at a particular time. A snapshot can be used as a
 * checkpoint for data restoration or a data source for a new table.
 * Note: This is a private alpha release of Cloud Bigtable snapshots. This
 * feature is not currently available to most Cloud Bigtable customers. This
 * feature might be changed in backward-incompatible ways and is not recommended
 * for production use. It is not subject to any SLA or deprecation policy.
 *
 * Generated from protobuf message <code>google.bigtable.admin.v2.Snapshot</code>
 */
class Snapshot extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. The unique name of the snapshot.
     * Values are of the form
     * `projects/{project}/instances/{instance}/clusters/{cluster}/snapshots/{snapshot}`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * Output only. The source table at the time the snapshot was taken.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Table source_table = 2;</code>
     */
    private $source_table = null;
    /**
     * Output only. The size of the data in the source table at the time the
     * snapshot was taken. In some cases, this value may be computed
     * asynchronously via a background process and a placeholder of 0 will be used
     * in the meantime.
     *
     * Generated from protobuf field <code>int64 data_size_bytes = 3;</code>
     */
    private $data_size_bytes = 0;
    /**
     * Output only. The time when the snapshot is created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4;</code>
     */
    private $create_time = null;
    /**
     * Output only. The time when the snapshot will be deleted. The maximum amount
     * of time a snapshot can stay active is 365 days. If 'ttl' is not specified,
     * the default maximum of 365 days will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp delete_time = 5;</code>
     */
    private $delete_time = null;
    /**
     * Output only. The current state of the snapshot.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Snapshot.State state = 6;</code>
     */
    private $state = 0;
    /**
     * Output only. Description of the snapshot.
     *
     * Generated from protobuf field <code>string description = 7;</code>
     */
    private $description = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. The unique name of the snapshot.
     *           Values are of the form
     *           `projects/{project}/instances/{instance}/clusters/{cluster}/snapshots/{snapshot}`.
     *     @type \Google\Cloud\Bigtable\Admin\V2\Table $source_table
     *           Output only. The source table at the time the snapshot was taken.
     *     @type int|string $data_size_bytes
     *           Output only. The size of the data in the source table at the time the
     *           snapshot was taken. In some cases, this value may be computed
     *           asynchronously via a background process and a placeholder of 0 will be used
     *           in the meantime.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. The time when the snapshot is created.
     *     @type \Google\Protobuf\Timestamp $delete_time
     *           Output only. The time when the snapshot will be deleted. The maximum amount
     *           of time a snapshot can stay active is 365 days. If 'ttl' is not specified,
     *           the default maximum of 365 days will be used.
     *     @type int $state
     *           Output only. The current state of the snapshot.
     *     @type string $description
     *           Output only. Description of the snapshot.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Bigtable\Admin\V2\Table::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. The unique name of the snapshot.
     * Values are of the form
     * `projects/{project}/instances/{instance}/clusters/{cluster}/snapshots/{snapshot}`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. The unique name of the snapshot.
     * Values are of the form
     * `projects/{project}/instances/{instance}/clusters/{cluster}/snapshots/{snapshot}`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
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
     * Output only. The source table at the time the snapshot was taken.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Table source_table = 2;</code>
     * @return \Google\Cloud\Bigtable\Admin\V2\Table|null
     */
    public function getSourceTable()
    {
        return $this->source_table;
    }

    public function hasSourceTable()
    {
        return isset($this->source_table);
    }

    public function clearSourceTable()
    {
        unset($this->source_table);
    }

    /**
     * Output only. The source table at the time the snapshot was taken.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Table source_table = 2;</code>
     * @param \Google\Cloud\Bigtable\Admin\V2\Table $var
     * @return $this
     */
    public function setSourceTable($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Bigtable\Admin\V2\Table::class);
        $this->source_table = $var;

        return $this;
    }

    /**
     * Output only. The size of the data in the source table at the time the
     * snapshot was taken. In some cases, this value may be computed
     * asynchronously via a background process and a placeholder of 0 will be used
     * in the meantime.
     *
     * Generated from protobuf field <code>int64 data_size_bytes = 3;</code>
     * @return int|string
     */
    public function getDataSizeBytes()
    {
        return $this->data_size_bytes;
    }

    /**
     * Output only. The size of the data in the source table at the time the
     * snapshot was taken. In some cases, this value may be computed
     * asynchronously via a background process and a placeholder of 0 will be used
     * in the meantime.
     *
     * Generated from protobuf field <code>int64 data_size_bytes = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setDataSizeBytes($var)
    {
        GPBUtil::checkInt64($var);
        $this->data_size_bytes = $var;

        return $this;
    }

    /**
     * Output only. The time when the snapshot is created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4;</code>
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
     * Output only. The time when the snapshot is created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 4;</code>
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
     * Output only. The time when the snapshot will be deleted. The maximum amount
     * of time a snapshot can stay active is 365 days. If 'ttl' is not specified,
     * the default maximum of 365 days will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp delete_time = 5;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getDeleteTime()
    {
        return $this->delete_time;
    }

    public function hasDeleteTime()
    {
        return isset($this->delete_time);
    }

    public function clearDeleteTime()
    {
        unset($this->delete_time);
    }

    /**
     * Output only. The time when the snapshot will be deleted. The maximum amount
     * of time a snapshot can stay active is 365 days. If 'ttl' is not specified,
     * the default maximum of 365 days will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp delete_time = 5;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setDeleteTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->delete_time = $var;

        return $this;
    }

    /**
     * Output only. The current state of the snapshot.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Snapshot.State state = 6;</code>
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Output only. The current state of the snapshot.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.Snapshot.State state = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setState($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Bigtable\Admin\V2\Snapshot\State::class);
        $this->state = $var;

        return $this;
    }

    /**
     * Output only. Description of the snapshot.
     *
     * Generated from protobuf field <code>string description = 7;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Output only. Description of the snapshot.
     *
     * Generated from protobuf field <code>string description = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

}

