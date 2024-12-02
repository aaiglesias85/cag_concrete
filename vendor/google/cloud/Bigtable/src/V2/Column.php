<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/bigtable/v2/data.proto

namespace Google\Cloud\Bigtable\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Specifies (some of) the contents of a single row/column intersection of a
 * table.
 *
 * Generated from protobuf message <code>google.bigtable.v2.Column</code>
 */
class Column extends \Google\Protobuf\Internal\Message
{
    /**
     * The unique key which identifies this column within its family. This is the
     * same key that's used to identify the column in, for example, a RowFilter
     * which sets its `column_qualifier_regex_filter` field.
     * May contain any byte string, including the empty string, up to 16kiB in
     * length.
     *
     * Generated from protobuf field <code>bytes qualifier = 1;</code>
     */
    private $qualifier = '';
    /**
     * Must not be empty. Sorted in order of decreasing "timestamp_micros".
     *
     * Generated from protobuf field <code>repeated .google.bigtable.v2.Cell cells = 2;</code>
     */
    private $cells;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $qualifier
     *           The unique key which identifies this column within its family. This is the
     *           same key that's used to identify the column in, for example, a RowFilter
     *           which sets its `column_qualifier_regex_filter` field.
     *           May contain any byte string, including the empty string, up to 16kiB in
     *           length.
     *     @type \Google\Cloud\Bigtable\V2\Cell[]|\Google\Protobuf\Internal\RepeatedField $cells
     *           Must not be empty. Sorted in order of decreasing "timestamp_micros".
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Bigtable\V2\Data::initOnce();
        parent::__construct($data);
    }

    /**
     * The unique key which identifies this column within its family. This is the
     * same key that's used to identify the column in, for example, a RowFilter
     * which sets its `column_qualifier_regex_filter` field.
     * May contain any byte string, including the empty string, up to 16kiB in
     * length.
     *
     * Generated from protobuf field <code>bytes qualifier = 1;</code>
     * @return string
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * The unique key which identifies this column within its family. This is the
     * same key that's used to identify the column in, for example, a RowFilter
     * which sets its `column_qualifier_regex_filter` field.
     * May contain any byte string, including the empty string, up to 16kiB in
     * length.
     *
     * Generated from protobuf field <code>bytes qualifier = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setQualifier($var)
    {
        GPBUtil::checkString($var, False);
        $this->qualifier = $var;

        return $this;
    }

    /**
     * Must not be empty. Sorted in order of decreasing "timestamp_micros".
     *
     * Generated from protobuf field <code>repeated .google.bigtable.v2.Cell cells = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * Must not be empty. Sorted in order of decreasing "timestamp_micros".
     *
     * Generated from protobuf field <code>repeated .google.bigtable.v2.Cell cells = 2;</code>
     * @param \Google\Cloud\Bigtable\V2\Cell[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setCells($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Bigtable\V2\Cell::class);
        $this->cells = $arr;

        return $this;
    }

}

