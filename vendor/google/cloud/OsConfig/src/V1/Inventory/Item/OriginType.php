<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/osconfig/v1/inventory.proto

namespace Google\Cloud\OsConfig\V1\Inventory\Item;

use UnexpectedValueException;

/**
 * The origin of a specific inventory item.
 *
 * Protobuf type <code>google.cloud.osconfig.v1.Inventory.Item.OriginType</code>
 */
class OriginType
{
    /**
     * Invalid. An origin type must be specified.
     *
     * Generated from protobuf enum <code>ORIGIN_TYPE_UNSPECIFIED = 0;</code>
     */
    const ORIGIN_TYPE_UNSPECIFIED = 0;
    /**
     * This inventory item was discovered as the result of the agent
     * reporting inventory via the reporting API.
     *
     * Generated from protobuf enum <code>INVENTORY_REPORT = 1;</code>
     */
    const INVENTORY_REPORT = 1;

    private static $valueToName = [
        self::ORIGIN_TYPE_UNSPECIFIED => 'ORIGIN_TYPE_UNSPECIFIED',
        self::INVENTORY_REPORT => 'INVENTORY_REPORT',
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


