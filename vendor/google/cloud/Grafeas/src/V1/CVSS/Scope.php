<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grafeas/v1/cvss.proto

namespace Grafeas\V1\CVSS;

use UnexpectedValueException;

/**
 * Protobuf type <code>grafeas.v1.CVSS.Scope</code>
 */
class Scope
{
    /**
     * Generated from protobuf enum <code>SCOPE_UNSPECIFIED = 0;</code>
     */
    const SCOPE_UNSPECIFIED = 0;
    /**
     * Generated from protobuf enum <code>SCOPE_UNCHANGED = 1;</code>
     */
    const SCOPE_UNCHANGED = 1;
    /**
     * Generated from protobuf enum <code>SCOPE_CHANGED = 2;</code>
     */
    const SCOPE_CHANGED = 2;

    private static $valueToName = [
        self::SCOPE_UNSPECIFIED => 'SCOPE_UNSPECIFIED',
        self::SCOPE_UNCHANGED => 'SCOPE_UNCHANGED',
        self::SCOPE_CHANGED => 'SCOPE_CHANGED',
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


