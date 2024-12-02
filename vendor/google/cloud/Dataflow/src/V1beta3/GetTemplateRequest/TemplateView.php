<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/templates.proto

namespace Google\Cloud\Dataflow\V1beta3\GetTemplateRequest;

use UnexpectedValueException;

/**
 * The various views of a template that may be retrieved.
 *
 * Protobuf type <code>google.dataflow.v1beta3.GetTemplateRequest.TemplateView</code>
 */
class TemplateView
{
    /**
     * Template view that retrieves only the metadata associated with the
     * template.
     *
     * Generated from protobuf enum <code>METADATA_ONLY = 0;</code>
     */
    const METADATA_ONLY = 0;

    private static $valueToName = [
        self::METADATA_ONLY => 'METADATA_ONLY',
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
class_alias(TemplateView::class, \Google\Cloud\Dataflow\V1beta3\GetTemplateRequest_TemplateView::class);

