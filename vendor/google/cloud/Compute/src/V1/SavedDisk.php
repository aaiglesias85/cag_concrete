<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * An instance-attached disk resource.
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.SavedDisk</code>
 */
class SavedDisk extends \Google\Protobuf\Internal\Message
{
    /**
     * [Output Only] Type of the resource. Always compute#savedDisk for attached disks.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     */
    private $kind = null;
    /**
     * Specifies a URL of the disk attached to the source instance.
     *
     * Generated from protobuf field <code>optional string source_disk = 451753793;</code>
     */
    private $source_disk = null;
    /**
     * [Output Only] Size of the individual disk snapshot used by this machine image.
     *
     * Generated from protobuf field <code>optional int64 storage_bytes = 424631719;</code>
     */
    private $storage_bytes = null;
    /**
     * [Output Only] An indicator whether storageBytes is in a stable state or it is being adjusted as a result of shared storage reallocation. This status can either be UPDATING, meaning the size of the snapshot is being updated, or UP_TO_DATE, meaning the size of the snapshot is up-to-date.
     * Check the StorageBytesStatus enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string storage_bytes_status = 490739082;</code>
     */
    private $storage_bytes_status = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $kind
     *           [Output Only] Type of the resource. Always compute#savedDisk for attached disks.
     *     @type string $source_disk
     *           Specifies a URL of the disk attached to the source instance.
     *     @type int|string $storage_bytes
     *           [Output Only] Size of the individual disk snapshot used by this machine image.
     *     @type string $storage_bytes_status
     *           [Output Only] An indicator whether storageBytes is in a stable state or it is being adjusted as a result of shared storage reallocation. This status can either be UPDATING, meaning the size of the snapshot is being updated, or UP_TO_DATE, meaning the size of the snapshot is up-to-date.
     *           Check the StorageBytesStatus enum for the list of possible values.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * [Output Only] Type of the resource. Always compute#savedDisk for attached disks.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     * @return string
     */
    public function getKind()
    {
        return isset($this->kind) ? $this->kind : '';
    }

    public function hasKind()
    {
        return isset($this->kind);
    }

    public function clearKind()
    {
        unset($this->kind);
    }

    /**
     * [Output Only] Type of the resource. Always compute#savedDisk for attached disks.
     *
     * Generated from protobuf field <code>optional string kind = 3292052;</code>
     * @param string $var
     * @return $this
     */
    public function setKind($var)
    {
        GPBUtil::checkString($var, True);
        $this->kind = $var;

        return $this;
    }

    /**
     * Specifies a URL of the disk attached to the source instance.
     *
     * Generated from protobuf field <code>optional string source_disk = 451753793;</code>
     * @return string
     */
    public function getSourceDisk()
    {
        return isset($this->source_disk) ? $this->source_disk : '';
    }

    public function hasSourceDisk()
    {
        return isset($this->source_disk);
    }

    public function clearSourceDisk()
    {
        unset($this->source_disk);
    }

    /**
     * Specifies a URL of the disk attached to the source instance.
     *
     * Generated from protobuf field <code>optional string source_disk = 451753793;</code>
     * @param string $var
     * @return $this
     */
    public function setSourceDisk($var)
    {
        GPBUtil::checkString($var, True);
        $this->source_disk = $var;

        return $this;
    }

    /**
     * [Output Only] Size of the individual disk snapshot used by this machine image.
     *
     * Generated from protobuf field <code>optional int64 storage_bytes = 424631719;</code>
     * @return int|string
     */
    public function getStorageBytes()
    {
        return isset($this->storage_bytes) ? $this->storage_bytes : 0;
    }

    public function hasStorageBytes()
    {
        return isset($this->storage_bytes);
    }

    public function clearStorageBytes()
    {
        unset($this->storage_bytes);
    }

    /**
     * [Output Only] Size of the individual disk snapshot used by this machine image.
     *
     * Generated from protobuf field <code>optional int64 storage_bytes = 424631719;</code>
     * @param int|string $var
     * @return $this
     */
    public function setStorageBytes($var)
    {
        GPBUtil::checkInt64($var);
        $this->storage_bytes = $var;

        return $this;
    }

    /**
     * [Output Only] An indicator whether storageBytes is in a stable state or it is being adjusted as a result of shared storage reallocation. This status can either be UPDATING, meaning the size of the snapshot is being updated, or UP_TO_DATE, meaning the size of the snapshot is up-to-date.
     * Check the StorageBytesStatus enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string storage_bytes_status = 490739082;</code>
     * @return string
     */
    public function getStorageBytesStatus()
    {
        return isset($this->storage_bytes_status) ? $this->storage_bytes_status : '';
    }

    public function hasStorageBytesStatus()
    {
        return isset($this->storage_bytes_status);
    }

    public function clearStorageBytesStatus()
    {
        unset($this->storage_bytes_status);
    }

    /**
     * [Output Only] An indicator whether storageBytes is in a stable state or it is being adjusted as a result of shared storage reallocation. This status can either be UPDATING, meaning the size of the snapshot is being updated, or UP_TO_DATE, meaning the size of the snapshot is up-to-date.
     * Check the StorageBytesStatus enum for the list of possible values.
     *
     * Generated from protobuf field <code>optional string storage_bytes_status = 490739082;</code>
     * @param string $var
     * @return $this
     */
    public function setStorageBytesStatus($var)
    {
        GPBUtil::checkString($var, True);
        $this->storage_bytes_status = $var;

        return $this;
    }

}

