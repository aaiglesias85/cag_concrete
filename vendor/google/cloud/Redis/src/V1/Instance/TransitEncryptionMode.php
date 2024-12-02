<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/redis/v1/cloud_redis.proto

namespace Google\Cloud\Redis\V1\Instance;

use UnexpectedValueException;

/**
 * Available TLS modes.
 *
 * Protobuf type <code>google.cloud.redis.v1.Instance.TransitEncryptionMode</code>
 */
class TransitEncryptionMode
{
    /**
     * Not set.
     *
     * Generated from protobuf enum <code>TRANSIT_ENCRYPTION_MODE_UNSPECIFIED = 0;</code>
     */
    const TRANSIT_ENCRYPTION_MODE_UNSPECIFIED = 0;
    /**
     * Client to Server traffic encryption enabled with server authentication.
     *
     * Generated from protobuf enum <code>SERVER_AUTHENTICATION = 1;</code>
     */
    const SERVER_AUTHENTICATION = 1;
    /**
     * TLS is disabled for the instance.
     *
     * Generated from protobuf enum <code>DISABLED = 2;</code>
     */
    const DISABLED = 2;

    private static $valueToName = [
        self::TRANSIT_ENCRYPTION_MODE_UNSPECIFIED => 'TRANSIT_ENCRYPTION_MODE_UNSPECIFIED',
        self::SERVER_AUTHENTICATION => 'SERVER_AUTHENTICATION',
        self::DISABLED => 'DISABLED',
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
class_alias(TransitEncryptionMode::class, \Google\Cloud\Redis\V1\Instance_TransitEncryptionMode::class);

