<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recommender/v1/recommendation.proto

namespace Google\Cloud\Recommender\V1\RecommendationStateInfo;

use UnexpectedValueException;

/**
 * Represents Recommendation State.
 *
 * Protobuf type <code>google.cloud.recommender.v1.RecommendationStateInfo.State</code>
 */
class State
{
    /**
     * Default state. Don't use directly.
     *
     * Generated from protobuf enum <code>STATE_UNSPECIFIED = 0;</code>
     */
    const STATE_UNSPECIFIED = 0;
    /**
     * Recommendation is active and can be applied. Recommendations content can
     * be updated by Google.
     * ACTIVE recommendations can be marked as CLAIMED, SUCCEEDED, or FAILED.
     *
     * Generated from protobuf enum <code>ACTIVE = 1;</code>
     */
    const ACTIVE = 1;
    /**
     * Recommendation is in claimed state. Recommendations content is
     * immutable and cannot be updated by Google.
     * CLAIMED recommendations can be marked as CLAIMED, SUCCEEDED, or FAILED.
     *
     * Generated from protobuf enum <code>CLAIMED = 6;</code>
     */
    const CLAIMED = 6;
    /**
     * Recommendation is in succeeded state. Recommendations content is
     * immutable and cannot be updated by Google.
     * SUCCEEDED recommendations can be marked as SUCCEEDED, or FAILED.
     *
     * Generated from protobuf enum <code>SUCCEEDED = 3;</code>
     */
    const SUCCEEDED = 3;
    /**
     * Recommendation is in failed state. Recommendations content is immutable
     * and cannot be updated by Google.
     * FAILED recommendations can be marked as SUCCEEDED, or FAILED.
     *
     * Generated from protobuf enum <code>FAILED = 4;</code>
     */
    const FAILED = 4;
    /**
     * Recommendation is in dismissed state. Recommendation content can be
     * updated by Google.
     * DISMISSED recommendations can be marked as ACTIVE.
     *
     * Generated from protobuf enum <code>DISMISSED = 5;</code>
     */
    const DISMISSED = 5;

    private static $valueToName = [
        self::STATE_UNSPECIFIED => 'STATE_UNSPECIFIED',
        self::ACTIVE => 'ACTIVE',
        self::CLAIMED => 'CLAIMED',
        self::SUCCEEDED => 'SUCCEEDED',
        self::FAILED => 'FAILED',
        self::DISMISSED => 'DISMISSED',
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
class_alias(State::class, \Google\Cloud\Recommender\V1\RecommendationStateInfo_State::class);

