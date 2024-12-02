<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/storagetransfer/v1/transfer_types.proto

namespace Google\Cloud\StorageTransfer\V1\MetadataOptions;

use UnexpectedValueException;

/**
 * Options for handling file UID attribute.
 *
 * Protobuf type <code>google.storagetransfer.v1.MetadataOptions.UID</code>
 */
class UID
{
    /**
     * UID behavior is unspecified.
     *
     * Generated from protobuf enum <code>UID_UNSPECIFIED = 0;</code>
     */
    const UID_UNSPECIFIED = 0;
    /**
     * Do not preserve UID during a transfer job.
     *
     * Generated from protobuf enum <code>UID_SKIP = 1;</code>
     */
    const UID_SKIP = 1;
    /**
     * Preserve UID during a transfer job.
     *
     * Generated from protobuf enum <code>UID_NUMBER = 2;</code>
     */
    const UID_NUMBER = 2;

    private static $valueToName = [
        self::UID_UNSPECIFIED => 'UID_UNSPECIFIED',
        self::UID_SKIP => 'UID_SKIP',
        self::UID_NUMBER => 'UID_NUMBER',
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


