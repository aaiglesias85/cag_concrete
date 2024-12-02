<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/admin/v1/field.proto

namespace Google\Cloud\Firestore\Admin\V1\Field;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The index configuration for this field.
 *
 * Generated from protobuf message <code>google.firestore.admin.v1.Field.IndexConfig</code>
 */
class IndexConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * The indexes supported for this field.
     *
     * Generated from protobuf field <code>repeated .google.firestore.admin.v1.Index indexes = 1;</code>
     */
    private $indexes;
    /**
     * Output only. When true, the `Field`'s index configuration is set from the
     * configuration specified by the `ancestor_field`.
     * When false, the `Field`'s index configuration is defined explicitly.
     *
     * Generated from protobuf field <code>bool uses_ancestor_config = 2;</code>
     */
    private $uses_ancestor_config = false;
    /**
     * Output only. Specifies the resource name of the `Field` from which this field's
     * index configuration is set (when `uses_ancestor_config` is true),
     * or from which it *would* be set if this field had no index configuration
     * (when `uses_ancestor_config` is false).
     *
     * Generated from protobuf field <code>string ancestor_field = 3;</code>
     */
    private $ancestor_field = '';
    /**
     * Output only
     * When true, the `Field`'s index configuration is in the process of being
     * reverted. Once complete, the index config will transition to the same
     * state as the field specified by `ancestor_field`, at which point
     * `uses_ancestor_config` will be `true` and `reverting` will be `false`.
     *
     * Generated from protobuf field <code>bool reverting = 4;</code>
     */
    private $reverting = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Firestore\Admin\V1\Index[]|\Google\Protobuf\Internal\RepeatedField $indexes
     *           The indexes supported for this field.
     *     @type bool $uses_ancestor_config
     *           Output only. When true, the `Field`'s index configuration is set from the
     *           configuration specified by the `ancestor_field`.
     *           When false, the `Field`'s index configuration is defined explicitly.
     *     @type string $ancestor_field
     *           Output only. Specifies the resource name of the `Field` from which this field's
     *           index configuration is set (when `uses_ancestor_config` is true),
     *           or from which it *would* be set if this field had no index configuration
     *           (when `uses_ancestor_config` is false).
     *     @type bool $reverting
     *           Output only
     *           When true, the `Field`'s index configuration is in the process of being
     *           reverted. Once complete, the index config will transition to the same
     *           state as the field specified by `ancestor_field`, at which point
     *           `uses_ancestor_config` will be `true` and `reverting` will be `false`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Firestore\Admin\V1\Field::initOnce();
        parent::__construct($data);
    }

    /**
     * The indexes supported for this field.
     *
     * Generated from protobuf field <code>repeated .google.firestore.admin.v1.Index indexes = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * The indexes supported for this field.
     *
     * Generated from protobuf field <code>repeated .google.firestore.admin.v1.Index indexes = 1;</code>
     * @param \Google\Cloud\Firestore\Admin\V1\Index[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setIndexes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Firestore\Admin\V1\Index::class);
        $this->indexes = $arr;

        return $this;
    }

    /**
     * Output only. When true, the `Field`'s index configuration is set from the
     * configuration specified by the `ancestor_field`.
     * When false, the `Field`'s index configuration is defined explicitly.
     *
     * Generated from protobuf field <code>bool uses_ancestor_config = 2;</code>
     * @return bool
     */
    public function getUsesAncestorConfig()
    {
        return $this->uses_ancestor_config;
    }

    /**
     * Output only. When true, the `Field`'s index configuration is set from the
     * configuration specified by the `ancestor_field`.
     * When false, the `Field`'s index configuration is defined explicitly.
     *
     * Generated from protobuf field <code>bool uses_ancestor_config = 2;</code>
     * @param bool $var
     * @return $this
     */
    public function setUsesAncestorConfig($var)
    {
        GPBUtil::checkBool($var);
        $this->uses_ancestor_config = $var;

        return $this;
    }

    /**
     * Output only. Specifies the resource name of the `Field` from which this field's
     * index configuration is set (when `uses_ancestor_config` is true),
     * or from which it *would* be set if this field had no index configuration
     * (when `uses_ancestor_config` is false).
     *
     * Generated from protobuf field <code>string ancestor_field = 3;</code>
     * @return string
     */
    public function getAncestorField()
    {
        return $this->ancestor_field;
    }

    /**
     * Output only. Specifies the resource name of the `Field` from which this field's
     * index configuration is set (when `uses_ancestor_config` is true),
     * or from which it *would* be set if this field had no index configuration
     * (when `uses_ancestor_config` is false).
     *
     * Generated from protobuf field <code>string ancestor_field = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setAncestorField($var)
    {
        GPBUtil::checkString($var, True);
        $this->ancestor_field = $var;

        return $this;
    }

    /**
     * Output only
     * When true, the `Field`'s index configuration is in the process of being
     * reverted. Once complete, the index config will transition to the same
     * state as the field specified by `ancestor_field`, at which point
     * `uses_ancestor_config` will be `true` and `reverting` will be `false`.
     *
     * Generated from protobuf field <code>bool reverting = 4;</code>
     * @return bool
     */
    public function getReverting()
    {
        return $this->reverting;
    }

    /**
     * Output only
     * When true, the `Field`'s index configuration is in the process of being
     * reverted. Once complete, the index config will transition to the same
     * state as the field specified by `ancestor_field`, at which point
     * `uses_ancestor_config` will be `true` and `reverting` will be `false`.
     *
     * Generated from protobuf field <code>bool reverting = 4;</code>
     * @param bool $var
     * @return $this
     */
    public function setReverting($var)
    {
        GPBUtil::checkBool($var);
        $this->reverting = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(IndexConfig::class, \Google\Cloud\Firestore\Admin\V1\Field_IndexConfig::class);

