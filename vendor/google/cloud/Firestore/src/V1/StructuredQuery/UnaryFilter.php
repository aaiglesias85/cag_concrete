<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/v1/query.proto

namespace Google\Cloud\Firestore\V1\StructuredQuery;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A filter with a single operand.
 *
 * Generated from protobuf message <code>google.firestore.v1.StructuredQuery.UnaryFilter</code>
 */
class UnaryFilter extends \Google\Protobuf\Internal\Message
{
    /**
     * The unary operator to apply.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.UnaryFilter.Operator op = 1;</code>
     */
    private $op = 0;
    protected $operand_type;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $op
     *           The unary operator to apply.
     *     @type \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference $field
     *           The field to which to apply the operator.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Firestore\V1\Query::initOnce();
        parent::__construct($data);
    }

    /**
     * The unary operator to apply.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.UnaryFilter.Operator op = 1;</code>
     * @return int
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * The unary operator to apply.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.UnaryFilter.Operator op = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setOp($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Firestore\V1\StructuredQuery\UnaryFilter\Operator::class);
        $this->op = $var;

        return $this;
    }

    /**
     * The field to which to apply the operator.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.FieldReference field = 2;</code>
     * @return \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference|null
     */
    public function getField()
    {
        return $this->readOneof(2);
    }

    public function hasField()
    {
        return $this->hasOneof(2);
    }

    /**
     * The field to which to apply the operator.
     *
     * Generated from protobuf field <code>.google.firestore.v1.StructuredQuery.FieldReference field = 2;</code>
     * @param \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference $var
     * @return $this
     */
    public function setField($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\StructuredQuery\FieldReference::class);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getOperandType()
    {
        return $this->whichOneof("operand_type");
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(UnaryFilter::class, \Google\Cloud\Firestore\V1\StructuredQuery_UnaryFilter::class);

