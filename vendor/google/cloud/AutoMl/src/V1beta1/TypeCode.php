<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/automl/v1beta1/data_types.proto

namespace Google\Cloud\AutoMl\V1beta1;

use UnexpectedValueException;

/**
 * `TypeCode` is used as a part of
 * [DataType][google.cloud.automl.v1beta1.DataType].
 *
 * Protobuf type <code>google.cloud.automl.v1beta1.TypeCode</code>
 */
class TypeCode
{
    /**
     * Not specified. Should not be used.
     *
     * Generated from protobuf enum <code>TYPE_CODE_UNSPECIFIED = 0;</code>
     */
    const TYPE_CODE_UNSPECIFIED = 0;
    /**
     * Encoded as `number`, or the strings `"NaN"`, `"Infinity"`, or
     * `"-Infinity"`.
     *
     * Generated from protobuf enum <code>FLOAT64 = 3;</code>
     */
    const FLOAT64 = 3;
    /**
     * Must be between 0AD and 9999AD. Encoded as `string` according to
     * [time_format][google.cloud.automl.v1beta1.DataType.time_format], or, if
     * that format is not set, then in RFC 3339 `date-time` format, where
     * `time-offset` = `"Z"` (e.g. 1985-04-12T23:20:50.52Z).
     *
     * Generated from protobuf enum <code>TIMESTAMP = 4;</code>
     */
    const TIMESTAMP = 4;
    /**
     * Encoded as `string`.
     *
     * Generated from protobuf enum <code>STRING = 6;</code>
     */
    const STRING = 6;
    /**
     * Encoded as `list`, where the list elements are represented according to
     * [list_element_type][google.cloud.automl.v1beta1.DataType.list_element_type].
     *
     * Generated from protobuf enum <code>ARRAY = 8;</code>
     */
    const PBARRAY = 8;
    /**
     * Encoded as `struct`, where field values are represented according to
     * [struct_type][google.cloud.automl.v1beta1.DataType.struct_type].
     *
     * Generated from protobuf enum <code>STRUCT = 9;</code>
     */
    const STRUCT = 9;
    /**
     * Values of this type are not further understood by AutoML,
     * e.g. AutoML is unable to tell the order of values (as it could with
     * FLOAT64), or is unable to say if one value contains another (as it
     * could with STRING).
     * Encoded as `string` (bytes should be base64-encoded, as described in RFC
     * 4648, section 4).
     *
     * Generated from protobuf enum <code>CATEGORY = 10;</code>
     */
    const CATEGORY = 10;

    private static $valueToName = [
        self::TYPE_CODE_UNSPECIFIED => 'TYPE_CODE_UNSPECIFIED',
        self::FLOAT64 => 'FLOAT64',
        self::TIMESTAMP => 'TIMESTAMP',
        self::STRING => 'STRING',
        self::PBARRAY => 'PBARRAY',
        self::STRUCT => 'STRUCT',
        self::CATEGORY => 'CATEGORY',
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

