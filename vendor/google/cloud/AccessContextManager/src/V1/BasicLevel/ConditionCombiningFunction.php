<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/identity/accesscontextmanager/v1/access_level.proto

namespace Google\Identity\AccessContextManager\V1\BasicLevel;

use UnexpectedValueException;

/**
 * Options for how the `conditions` list should be combined to determine if
 * this `AccessLevel` is applied. Default is AND.
 *
 * Protobuf type <code>google.identity.accesscontextmanager.v1.BasicLevel.ConditionCombiningFunction</code>
 */
class ConditionCombiningFunction
{
    /**
     * All `Conditions` must be true for the `BasicLevel` to be true.
     *
     * Generated from protobuf enum <code>AND = 0;</code>
     */
    const PBAND = 0;
    /**
     * If at least one `Condition` is true, then the `BasicLevel` is true.
     *
     * Generated from protobuf enum <code>OR = 1;</code>
     */
    const PBOR = 1;

    private static $valueToName = [
        self::PBAND => 'PBAND',
        self::PBOR => 'PBOR',
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
class_alias(ConditionCombiningFunction::class, \Google\Identity\AccessContextManager\V1\BasicLevel_ConditionCombiningFunction::class);

