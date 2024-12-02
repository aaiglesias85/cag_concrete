<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/clouddms/v1/clouddms_resources.proto

namespace Google\Cloud\CloudDms\V1;

use UnexpectedValueException;

/**
 * The database providers.
 *
 * Protobuf type <code>google.cloud.clouddms.v1.DatabaseProvider</code>
 */
class DatabaseProvider
{
    /**
     * The database provider is unknown.
     *
     * Generated from protobuf enum <code>DATABASE_PROVIDER_UNSPECIFIED = 0;</code>
     */
    const DATABASE_PROVIDER_UNSPECIFIED = 0;
    /**
     * CloudSQL runs the database.
     *
     * Generated from protobuf enum <code>CLOUDSQL = 1;</code>
     */
    const CLOUDSQL = 1;
    /**
     * RDS runs the database.
     *
     * Generated from protobuf enum <code>RDS = 2;</code>
     */
    const RDS = 2;

    private static $valueToName = [
        self::DATABASE_PROVIDER_UNSPECIFIED => 'DATABASE_PROVIDER_UNSPECIFIED',
        self::CLOUDSQL => 'CLOUDSQL',
        self::RDS => 'RDS',
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

