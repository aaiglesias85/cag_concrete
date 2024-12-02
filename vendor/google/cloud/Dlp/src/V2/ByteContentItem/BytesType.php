<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2\ByteContentItem;

use UnexpectedValueException;

/**
 * The type of data being sent for inspection. To learn more, see
 * [Supported file
 * types](https://cloud.google.com/dlp/docs/supported-file-types).
 *
 * Protobuf type <code>google.privacy.dlp.v2.ByteContentItem.BytesType</code>
 */
class BytesType
{
    /**
     * Unused
     *
     * Generated from protobuf enum <code>BYTES_TYPE_UNSPECIFIED = 0;</code>
     */
    const BYTES_TYPE_UNSPECIFIED = 0;
    /**
     * Any image type.
     *
     * Generated from protobuf enum <code>IMAGE = 6;</code>
     */
    const IMAGE = 6;
    /**
     * jpeg
     *
     * Generated from protobuf enum <code>IMAGE_JPEG = 1;</code>
     */
    const IMAGE_JPEG = 1;
    /**
     * bmp
     *
     * Generated from protobuf enum <code>IMAGE_BMP = 2;</code>
     */
    const IMAGE_BMP = 2;
    /**
     * png
     *
     * Generated from protobuf enum <code>IMAGE_PNG = 3;</code>
     */
    const IMAGE_PNG = 3;
    /**
     * svg
     *
     * Generated from protobuf enum <code>IMAGE_SVG = 4;</code>
     */
    const IMAGE_SVG = 4;
    /**
     * plain text
     *
     * Generated from protobuf enum <code>TEXT_UTF8 = 5;</code>
     */
    const TEXT_UTF8 = 5;
    /**
     * docx, docm, dotx, dotm
     *
     * Generated from protobuf enum <code>WORD_DOCUMENT = 7;</code>
     */
    const WORD_DOCUMENT = 7;
    /**
     * pdf
     *
     * Generated from protobuf enum <code>PDF = 8;</code>
     */
    const PDF = 8;
    /**
     * pptx, pptm, potx, potm, pot
     *
     * Generated from protobuf enum <code>POWERPOINT_DOCUMENT = 9;</code>
     */
    const POWERPOINT_DOCUMENT = 9;
    /**
     * xlsx, xlsm, xltx, xltm
     *
     * Generated from protobuf enum <code>EXCEL_DOCUMENT = 10;</code>
     */
    const EXCEL_DOCUMENT = 10;
    /**
     * avro
     *
     * Generated from protobuf enum <code>AVRO = 11;</code>
     */
    const AVRO = 11;
    /**
     * csv
     *
     * Generated from protobuf enum <code>CSV = 12;</code>
     */
    const CSV = 12;
    /**
     * tsv
     *
     * Generated from protobuf enum <code>TSV = 13;</code>
     */
    const TSV = 13;

    private static $valueToName = [
        self::BYTES_TYPE_UNSPECIFIED => 'BYTES_TYPE_UNSPECIFIED',
        self::IMAGE => 'IMAGE',
        self::IMAGE_JPEG => 'IMAGE_JPEG',
        self::IMAGE_BMP => 'IMAGE_BMP',
        self::IMAGE_PNG => 'IMAGE_PNG',
        self::IMAGE_SVG => 'IMAGE_SVG',
        self::TEXT_UTF8 => 'TEXT_UTF8',
        self::WORD_DOCUMENT => 'WORD_DOCUMENT',
        self::PDF => 'PDF',
        self::POWERPOINT_DOCUMENT => 'POWERPOINT_DOCUMENT',
        self::EXCEL_DOCUMENT => 'EXCEL_DOCUMENT',
        self::AVRO => 'AVRO',
        self::CSV => 'CSV',
        self::TSV => 'TSV',
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
class_alias(BytesType::class, \Google\Cloud\Dlp\V2\ByteContentItem_BytesType::class);

