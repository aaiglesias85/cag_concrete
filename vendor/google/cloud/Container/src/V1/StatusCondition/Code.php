<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/container/v1/cluster_service.proto

namespace Google\Cloud\Container\V1\StatusCondition;

use UnexpectedValueException;

/**
 * Code for each condition
 *
 * Protobuf type <code>google.container.v1.StatusCondition.Code</code>
 */
class Code
{
    /**
     * UNKNOWN indicates a generic condition.
     *
     * Generated from protobuf enum <code>UNKNOWN = 0;</code>
     */
    const UNKNOWN = 0;
    /**
     * GCE_STOCKOUT indicates that Google Compute Engine resources are
     * temporarily unavailable.
     *
     * Generated from protobuf enum <code>GCE_STOCKOUT = 1;</code>
     */
    const GCE_STOCKOUT = 1;
    /**
     * GKE_SERVICE_ACCOUNT_DELETED indicates that the user deleted their robot
     * service account.
     *
     * Generated from protobuf enum <code>GKE_SERVICE_ACCOUNT_DELETED = 2;</code>
     */
    const GKE_SERVICE_ACCOUNT_DELETED = 2;
    /**
     * Google Compute Engine quota was exceeded.
     *
     * Generated from protobuf enum <code>GCE_QUOTA_EXCEEDED = 3;</code>
     */
    const GCE_QUOTA_EXCEEDED = 3;
    /**
     * Cluster state was manually changed by an SRE due to a system logic error.
     *
     * Generated from protobuf enum <code>SET_BY_OPERATOR = 4;</code>
     */
    const SET_BY_OPERATOR = 4;
    /**
     * Unable to perform an encrypt operation against the CloudKMS key used for
     * etcd level encryption.
     *
     * Generated from protobuf enum <code>CLOUD_KMS_KEY_ERROR = 7;</code>
     */
    const CLOUD_KMS_KEY_ERROR = 7;
    /**
     * Cluster CA is expiring soon.
     *
     * Generated from protobuf enum <code>CA_EXPIRING = 9;</code>
     */
    const CA_EXPIRING = 9;

    private static $valueToName = [
        self::UNKNOWN => 'UNKNOWN',
        self::GCE_STOCKOUT => 'GCE_STOCKOUT',
        self::GKE_SERVICE_ACCOUNT_DELETED => 'GKE_SERVICE_ACCOUNT_DELETED',
        self::GCE_QUOTA_EXCEEDED => 'GCE_QUOTA_EXCEEDED',
        self::SET_BY_OPERATOR => 'SET_BY_OPERATOR',
        self::CLOUD_KMS_KEY_ERROR => 'CLOUD_KMS_KEY_ERROR',
        self::CA_EXPIRING => 'CA_EXPIRING',
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
class_alias(Code::class, \Google\Cloud\Container\V1\StatusCondition_Code::class);

