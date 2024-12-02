<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/storagetransfer/v1/transfer_types.proto

namespace Google\Cloud\StorageTransfer\V1\TransferOperation;

use UnexpectedValueException;

/**
 * The status of a TransferOperation.
 *
 * Protobuf type <code>google.storagetransfer.v1.TransferOperation.Status</code>
 */
class Status
{
    /**
     * Zero is an illegal value.
     *
     * Generated from protobuf enum <code>STATUS_UNSPECIFIED = 0;</code>
     */
    const STATUS_UNSPECIFIED = 0;
    /**
     * In progress.
     *
     * Generated from protobuf enum <code>IN_PROGRESS = 1;</code>
     */
    const IN_PROGRESS = 1;
    /**
     * Paused.
     *
     * Generated from protobuf enum <code>PAUSED = 2;</code>
     */
    const PAUSED = 2;
    /**
     * Completed successfully.
     *
     * Generated from protobuf enum <code>SUCCESS = 3;</code>
     */
    const SUCCESS = 3;
    /**
     * Terminated due to an unrecoverable failure.
     *
     * Generated from protobuf enum <code>FAILED = 4;</code>
     */
    const FAILED = 4;
    /**
     * Aborted by the user.
     *
     * Generated from protobuf enum <code>ABORTED = 5;</code>
     */
    const ABORTED = 5;
    /**
     * Temporarily delayed by the system. No user action is required.
     *
     * Generated from protobuf enum <code>QUEUED = 6;</code>
     */
    const QUEUED = 6;

    private static $valueToName = [
        self::STATUS_UNSPECIFIED => 'STATUS_UNSPECIFIED',
        self::IN_PROGRESS => 'IN_PROGRESS',
        self::PAUSED => 'PAUSED',
        self::SUCCESS => 'SUCCESS',
        self::FAILED => 'FAILED',
        self::ABORTED => 'ABORTED',
        self::QUEUED => 'QUEUED',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}


