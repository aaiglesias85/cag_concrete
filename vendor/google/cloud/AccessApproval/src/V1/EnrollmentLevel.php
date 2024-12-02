<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/accessapproval/v1/accessapproval.proto

namespace Google\Cloud\AccessApproval\V1;

use UnexpectedValueException;

/**
 * Represents the type of enrollment for a given service to Access Approval.
 *
 * Protobuf type <code>google.cloud.accessapproval.v1.EnrollmentLevel</code>
 */
class EnrollmentLevel
{
    /**
     * Default value for proto, shouldn't be used.
     *
     * Generated from protobuf enum <code>ENROLLMENT_LEVEL_UNSPECIFIED = 0;</code>
     */
    const ENROLLMENT_LEVEL_UNSPECIFIED = 0;
    /**
     * Service is enrolled in Access Approval for all requests
     *
     * Generated from protobuf enum <code>BLOCK_ALL = 1;</code>
     */
    const BLOCK_ALL = 1;

    private static $valueToName = [
        self::ENROLLMENT_LEVEL_UNSPECIFIED => 'ENROLLMENT_LEVEL_UNSPECIFIED',
        self::BLOCK_ALL => 'BLOCK_ALL',
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

