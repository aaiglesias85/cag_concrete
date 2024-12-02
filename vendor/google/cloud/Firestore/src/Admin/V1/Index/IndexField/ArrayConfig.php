<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/admin/v1/index.proto

namespace Google\Cloud\Firestore\Admin\V1\Index\IndexField;

use UnexpectedValueException;

/**
 * The supported array value configurations.
 *
 * Protobuf type <code>google.firestore.admin.v1.Index.IndexField.ArrayConfig</code>
 */
class ArrayConfig
{
    /**
     * The index does not support additional array queries.
     *
     * Generated from protobuf enum <code>ARRAY_CONFIG_UNSPECIFIED = 0;</code>
     */
    const ARRAY_CONFIG_UNSPECIFIED = 0;
    /**
     * The index supports array containment queries.
     *
     * Generated from protobuf enum <code>CONTAINS = 1;</code>
     */
    const CONTAINS = 1;

    private static $valueToName = [
        self::ARRAY_CONFIG_UNSPECIFIED => 'ARRAY_CONFIG_UNSPECIFIED',
        self::CONTAINS => 'CONTAINS',
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

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ArrayConfig::class, \Google\Cloud\Firestore\Admin\V1\Index_IndexField_ArrayConfig::class);

