<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/v1/query.proto

namespace Google\Cloud\Firestore\V1\StructuredQuery;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * An order on a field.
 *
 * Generated from protobuf message <code>google.firestore.v1.StructuredQuery.Order</code>
 */
class Order extends \Google\Protobuf\Internal\Message
{
    /**
     * The field to order by.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.FieldReference field = 1;</code>
     */
    private $field = null;
    /**
     * The direction to order by. Defaults to `ASCENDING`.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.Direction direction = 2;</code>
     */
    private $direction = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference $field
     *           The field to order by.
     *     @type int $direction
     *           The direction to order by. Defaults to `ASCENDING`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Firestore\V1\Query::initOnce();
        parent::__construct($data);
    }

    /**
     * The field to order by.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.FieldReference field = 1;</code>
     * @return \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference|null
     */
    public function getField()
    {
        return $this->field;
    }

    public function hasField()
    {
        return isset($this->field);
    }

    public function clearField()
    {
        unset($this->field);
    }

    /**
     * The field to order by.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.FieldReference field = 1;</code>
     * @param \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference $var
     * @return $this
     */
    public function setField($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference::class);
        $this->field = $var;

        return $this;
    }

    /**
     * The direction to order by. Defaults to `ASCENDING`.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.Direction direction = 2;</code>
     * @return int
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * The direction to order by. Defaults to `ASCENDING`.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.Direction direction = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setDirection($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Firestore\V1\StructuredQuery\Direction::class);
        $this->direction = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Order::class, \Google\Cloud\Firestore\V1\StructuredQuery_Order::class);

