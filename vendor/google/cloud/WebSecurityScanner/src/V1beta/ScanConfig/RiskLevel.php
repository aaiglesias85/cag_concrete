<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/websecurityscanner/v1beta/scan_config.proto

namespace Google\Cloud\WebSecurityScanner\V1beta\ScanConfig;

use UnexpectedValueException;

/**
 * Scan risk levels supported by Cloud Web Security Scanner. LOW impact
 * scanning will minimize requests with the potential to modify data. To
 * achieve the maximum scan coverage, NORMAL risk level is recommended.
 *
 * Protobuf type <code>google.cloud.websecurityscanner.v1beta.ScanConfig.RiskLevel</code>
 */
class RiskLevel
{
    /**
     * Use default, which is NORMAL.
     *
     * Generated from protobuf enum <code>RISK_LEVEL_UNSPECIFIED = 0;</code>
     */
    const RISK_LEVEL_UNSPECIFIED = 0;
    /**
     * Normal scanning (Recommended)
     *
     * Generated from protobuf enum <code>NORMAL = 1;</code>
     */
    const NORMAL = 1;
    /**
     * Lower impact scanning
     *
     * Generated from protobuf enum <code>LOW = 2;</code>
     */
    const LOW = 2;

    private static $valueToName = [
        self::RISK_LEVEL_UNSPECIFIED => 'RISK_LEVEL_UNSPECIFIED',
        self::NORMAL => 'NORMAL',
        self::LOW => 'LOW',
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


