<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datalabeling/v1beta1/annotation.proto

namespace Google\Cloud\DataLabeling\V1beta1;

use UnexpectedValueException;

/**
 * Specifies where the annotation comes from (whether it was provided by a
 * human labeler or a different source).
 *
 * Protobuf type <code>google.cloud.datalabeling.v1beta1.AnnotationSource</code>
 */
class AnnotationSource
{
    /**
     * Generated from protobuf enum <code>ANNOTATION_SOURCE_UNSPECIFIED = 0;</code>
     */
    const ANNOTATION_SOURCE_UNSPECIFIED = 0;
    /**
     * Answer is provided by a human contributor.
     *
     * Generated from protobuf enum <code>OPERATOR = 3;</code>
     */
    const OPERATOR = 3;

    private static $valueToName = [
        self::ANNOTATION_SOURCE_UNSPECIFIED => 'ANNOTATION_SOURCE_UNSPECIFIED',
        self::OPERATOR => 'OPERATOR',
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

