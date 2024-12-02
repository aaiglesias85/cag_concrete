<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/appengine/v1/version.proto

namespace Google\Cloud\AppEngine\V1\EndpointsApiService;

use UnexpectedValueException;

/**
 * Available rollout strategies.
 *
 * Protobuf type <code>google.appengine.v1.EndpointsApiService.RolloutStrategy</code>
 */
class RolloutStrategy
{
    /**
     * Not specified. Defaults to `FIXED`.
     *
     * Generated from protobuf enum <code>UNSPECIFIED_ROLLOUT_STRATEGY = 0;</code>
     */
    const UNSPECIFIED_ROLLOUT_STRATEGY = 0;
    /**
     * Endpoints service configuration ID will be fixed to the configuration ID
     * specified by `config_id`.
     *
     * Generated from protobuf enum <code>FIXED = 1;</code>
     */
    const FIXED = 1;
    /**
     * Endpoints service configuration ID will be updated with each rollout.
     *
     * Generated from protobuf enum <code>MANAGED = 2;</code>
     */
    const MANAGED = 2;

    private static $valueToName = [
        self::UNSPECIFIED_ROLLOUT_STRATEGY => 'UNSPECIFIED_ROLLOUT_STRATEGY',
        self::FIXED => 'FIXED',
        self::MANAGED => 'MANAGED',
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
class_alias(RolloutStrategy::class, \Google\Cloud\AppEngine\V1\EndpointsApiService_RolloutStrategy::class);

