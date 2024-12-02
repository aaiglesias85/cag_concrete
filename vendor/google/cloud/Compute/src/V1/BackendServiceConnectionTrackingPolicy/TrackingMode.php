<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1\BackendServiceConnectionTrackingPolicy;

use UnexpectedValueException;

/**
 * Specifies the key used for connection tracking. There are two options: - PER_CONNECTION: This is the default mode. The Connection Tracking is performed as per the Connection Key (default Hash Method) for the specific protocol. - PER_SESSION: The Connection Tracking is performed as per the configured Session Affinity. It matches the configured Session Affinity. For more details, see [Tracking Mode for Network Load Balancing](https://cloud.google.com/load-balancing/docs/network/networklb-backend-service#tracking-mode) and [Tracking Mode for Internal TCP/UDP Load Balancing](https://cloud.google.com/load-balancing/docs/internal#tracking-mode).
 *
 * Protobuf type <code>google.cloud.compute.v1.BackendServiceConnectionTrackingPolicy.TrackingMode</code>
 */
class TrackingMode
{
    /**
     * A value indicating that the enum field is not set.
     *
     * Generated from protobuf enum <code>UNDEFINED_TRACKING_MODE = 0;</code>
     */
    const UNDEFINED_TRACKING_MODE = 0;
    /**
     * Generated from protobuf enum <code>INVALID_TRACKING_MODE = 49234371;</code>
     */
    const INVALID_TRACKING_MODE = 49234371;
    /**
     * Generated from protobuf enum <code>PER_CONNECTION = 85162848;</code>
     */
    const PER_CONNECTION = 85162848;
    /**
     * Generated from protobuf enum <code>PER_SESSION = 182099252;</code>
     */
    const PER_SESSION = 182099252;

    private static $valueToName = [
        self::UNDEFINED_TRACKING_MODE => 'UNDEFINED_TRACKING_MODE',
        self::INVALID_TRACKING_MODE => 'INVALID_TRACKING_MODE',
        self::PER_CONNECTION => 'PER_CONNECTION',
        self::PER_SESSION => 'PER_SESSION',
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


