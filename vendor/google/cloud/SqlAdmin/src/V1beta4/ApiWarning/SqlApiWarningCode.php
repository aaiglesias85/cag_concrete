<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/sql/v1beta4/cloud_sql_resources.proto

namespace Google\Cloud\Sql\V1beta4\ApiWarning;

use UnexpectedValueException;

/**
 * Protobuf type <code>google.cloud.sql.v1beta4.ApiWarning.SqlApiWarningCode</code>
 */
class SqlApiWarningCode
{
    /**
     * An unknown or unset warning type from Cloud SQL API.
     *
     * Generated from protobuf enum <code>SQL_API_WARNING_CODE_UNSPECIFIED = 0;</code>
     */
    const SQL_API_WARNING_CODE_UNSPECIFIED = 0;
    /**
     * Warning when one or more regions are not reachable.  The returned result
     * set may be incomplete.
     *
     * Generated from protobuf enum <code>REGION_UNREACHABLE = 1;</code>
     */
    const REGION_UNREACHABLE = 1;

    private static $valueToName = [
        self::SQL_API_WARNING_CODE_UNSPECIFIED => 'SQL_API_WARNING_CODE_UNSPECIFIED',
        self::REGION_UNREACHABLE => 'REGION_UNREACHABLE',
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


