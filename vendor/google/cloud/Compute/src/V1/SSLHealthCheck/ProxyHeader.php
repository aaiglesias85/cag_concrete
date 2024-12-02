<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1\SSLHealthCheck;

use UnexpectedValueException;

/**
 * Specifies the type of proxy header to append before sending data to the backend, either NONE or PROXY_V1. The default is NONE.
 *
 * Protobuf type <code>google.cloud.compute.v1.SSLHealthCheck.ProxyHeader</code>
 */
class ProxyHeader
{
    /**
     * A value indicating that the enum field is not set.
     *
     * Generated from protobuf enum <code>UNDEFINED_PROXY_HEADER = 0;</code>
     */
    const UNDEFINED_PROXY_HEADER = 0;
    /**
     * Generated from protobuf enum <code>NONE = 2402104;</code>
     */
    const NONE = 2402104;
    /**
     * Generated from protobuf enum <code>PROXY_V1 = 334352940;</code>
     */
    const PROXY_V1 = 334352940;

    private static $valueToName = [
        self::UNDEFINED_PROXY_HEADER => 'UNDEFINED_PROXY_HEADER',
        self::NONE => 'NONE',
        self::PROXY_V1 => 'PROXY_V1',
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


