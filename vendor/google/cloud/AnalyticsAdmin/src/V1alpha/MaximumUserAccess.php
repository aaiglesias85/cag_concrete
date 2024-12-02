<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/resources.proto

namespace Google\Analytics\Admin\V1alpha;

use UnexpectedValueException;

/**
 * Maximum access settings that Firebase user receive on the linked Analytics
 * property.
 *
 * Protobuf type <code>google.analytics.admin.v1alpha.MaximumUserAccess</code>
 */
class MaximumUserAccess
{
    /**
     * Unspecified maximum user access.
     *
     * Generated from protobuf enum <code>MAXIMUM_USER_ACCESS_UNSPECIFIED = 0;</code>
     */
    const MAXIMUM_USER_ACCESS_UNSPECIFIED = 0;
    /**
     * Firebase users have no access to the Analytics property.
     *
     * Generated from protobuf enum <code>NO_ACCESS = 1;</code>
     */
    const NO_ACCESS = 1;
    /**
     * Firebase users have Read & Analyze access to the Analytics property.
     *
     * Generated from protobuf enum <code>READ_AND_ANALYZE = 2;</code>
     */
    const READ_AND_ANALYZE = 2;
    /**
     * Firebase users have edit access to the Analytics property, but may not
     * manage the Firebase link.
     *
     * Generated from protobuf enum <code>EDITOR_WITHOUT_LINK_MANAGEMENT = 3;</code>
     */
    const EDITOR_WITHOUT_LINK_MANAGEMENT = 3;
    /**
     * Firebase users have edit access to the Analytics property and may manage
     * the Firebase link.
     *
     * Generated from protobuf enum <code>EDITOR_INCLUDING_LINK_MANAGEMENT = 4;</code>
     */
    const EDITOR_INCLUDING_LINK_MANAGEMENT = 4;

    private static $valueToName = [
        self::MAXIMUM_USER_ACCESS_UNSPECIFIED => 'MAXIMUM_USER_ACCESS_UNSPECIFIED',
        self::NO_ACCESS => 'NO_ACCESS',
        self::READ_AND_ANALYZE => 'READ_AND_ANALYZE',
        self::EDITOR_WITHOUT_LINK_MANAGEMENT => 'EDITOR_WITHOUT_LINK_MANAGEMENT',
        self::EDITOR_INCLUDING_LINK_MANAGEMENT => 'EDITOR_INCLUDING_LINK_MANAGEMENT',
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

