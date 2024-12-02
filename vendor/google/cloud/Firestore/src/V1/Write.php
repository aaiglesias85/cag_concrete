<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/firestore/v1/write.proto

namespace Google\Cloud\Firestore\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A write on a document.
 *
 * Generated from protobuf message <code>google.firestore.v1.Write</code>
 */
class Write extends \Google\Protobuf\Internal\Message
{
    /**
     * The fields to update in this write.
     * This field can be set only when the operation is `update`.
     * If the mask is not set for an `update` and the document exists, any
     * existing data will be overwritten.
     * If the mask is set and the document on the server has fields not covered by
     * the mask, they are left unchanged.
     * Fields referenced in the mask, but not present in the input document, are
     * deleted from the document on the server.
     * The field paths in this mask must not contain a reserved field name.
     *
     * Generated from protobuf field <code>.google.firestore.v1.DocumentMask update_mask = 3;</code>
     */
    private $update_mask = null;
    /**
     * The transforms to perform after update.
     * This field can be set only when the operation is `update`. If present, this
     * write is equivalent to performing `update` and `transform` to the same
     * document atomically and in order.
     *
     * Generated from protobuf field <code>repeated .google.firestore.v1.DocumentTransform.FieldTransform update_transforms = 7;</code>
     */
    private $update_transforms;
    /**
     * An optional precondition on the document.
     * The write will fail if this is set and not met by the target document.
     *
     * Generated from protobuf field <code>.google.firestore.v1.Precondition current_document = 4;</code>
     */
    private $current_document = null;
    protected $operation;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Firestore\V1\Document $update
     *           A document to write.
     *     @type string $delete
     *           A document name to delete. In the format:
     *           `projects/{project_id}/databases/{database_id}/documents/{document_path}`.
     *     @type \Google\Cloud\Firestore\V1\DocumentTransform $transform
     *           Applies a transformation to a document.
     *     @type \Google\Cloud\Firestore\V1\DocumentMask $update_mask
     *           The fields to update in this write.
     *           This field can be set only when the operation is `update`.
     *           If the mask is not set for an `update` and the document exists, any
     *           existing data will be overwritten.
     *           If the mask is set and the document on the server has fields not covered by
     *           the mask, they are left unchanged.
     *           Fields referenced in the mask, but not present in the input document, are
     *           deleted from the document on the server.
     *           The field paths in this mask must not contain a reserved field name.
     *     @type \Google\Cloud\Firestore\V1\DocumentTransform\FieldTransform[]|\Google\Protobuf\Internal\RepeatedField $update_transforms
     *           The transforms to perform after update.
     *           This field can be set only when the operation is `update`. If present, this
     *           write is equivalent to performing `update` and `transform` to the same
     *           document atomically and in order.
     *     @type \Google\Cloud\Firestore\V1\Precondition $current_document
     *           An optional precondition on the document.
     *           The write will fail if this is set and not met by the target document.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Firestore\V1\Write::initOnce();
        parent::__construct($data);
    }

    /**
     * A document to write.
     *
     * Generated from protobuf field <code>.google.firestore.v1.Document update = 1;</code>
     * @return \Google\Cloud\Firestore\V1\Document|null
     */
    public function getUpdate()
    {
        return $this->readOneof(1);
    }

    public function hasUpdate()
    {
        return $this->hasOneof(1);
    }

    /**
     * A document to write.
     *
     * Generated from protobuf field <code>.google.firestore.v1.Document update = 1;</code>
     * @param \Google\Cloud\Firestore\V1\Document $var
     * @return $this
     */
    public function setUpdate($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\Document::class);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * A document name to delete. In the format:
     * `projects/{project_id}/databases/{database_id}/documents/{document_path}`.
     *
     * Generated from protobuf field <code>string delete = 2;</code>
     * @return string
     */
    public function getDelete()
    {
        return $this->readOneof(2);
    }

    public function hasDelete()
    {
        return $this->hasOneof(2);
    }

    /**
     * A document name to delete. In the format:
     * `projects/{project_id}/databases/{database_id}/documents/{document_path}`.
     *
     * Generated from protobuf field <code>string delete = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setDelete($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * Applies a transformation to a document.
     *
     * Generated from protobuf field <code>.google.firestore.v1.DocumentTransform transform = 6;</code>
     * @return \Google\Cloud\Firestore\V1\DocumentTransform|null
     */
    public function getTransform()
    {
        return $this->readOneof(6);
    }

    public function hasTransform()
    {
        return $this->hasOneof(6);
    }

    /**
     * Applies a transformation to a document.
     *
     * Generated from protobuf field <code>.google.firestore.v1.DocumentTransform transform = 6;</code>
     * @param \Google\Cloud\Firestore\V1\DocumentTransform $var
     * @return $this
     */
    public function setTransform($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\DocumentTransform::class);
        $this->writeOneof(6, $var);

        return $this;
    }

    /**
     * The fields to update in this write.
     * This field can be set only when the operation is `update`.
     * If the mask is not set for an `update` and the document exists, any
     * existing data will be overwritten.
     * If the mask is set and the document on the server has fields not covered by
     * the mask, they are left unchanged.
     * Fields referenced in the mask, but not present in the input document, are
     * deleted from the document on the server.
     * The field paths in this mask must not contain a reserved field name.
     *
     * Generated from protobuf field <code>.google.firestore.v1.DocumentMask update_mask = 3;</code>
     * @return \Google\Cloud\Firestore\V1\DocumentMask|null
     */
    public function getUpdateMask()
    {
        return $this->update_mask;
    }

    public function hasUpdateMask()
    {
        return isset($this->update_mask);
    }

    public function clearUpdateMask()
    {
        unset($this->update_mask);
    }

    /**
     * The fields to update in this write.
     * This field can be set only when the operation is `update`.
     * If the mask is not set for an `update` and the document exists, any
     * existing data will be overwritten.
     * If the mask is set and the document on the server has fields not covered by
     * the mask, they are left unchanged.
     * Fields referenced in the mask, but not present in the input document, are
     * deleted from the document on the server.
     * The field paths in this mask must not contain a reserved field name.
     *
     * Generated from protobuf field <code>.google.firestore.v1.DocumentMask update_mask = 3;</code>
     * @param \Google\Cloud\Firestore\V1\DocumentMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\DocumentMask::class);
        $this->update_mask = $var;

        return $this;
    }

    /**
     * The transforms to perform after update.
     * This field can be set only when the operation is `update`. If present, this
     * write is equivalent to performing `update` and `transform` to the same
     * document atomically and in order.
     *
     * Generated from protobuf field <code>repeated .google.firestore.v1.DocumentTransform.FieldTransform update_transforms = 7;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getUpdateTransforms()
    {
        return $this->update_transforms;
    }

    /**
     * The transforms to perform after update.
     * This field can be set only when the operation is `update`. If present, this
     * write is equivalent to performing `update` and `transform` to the same
     * document atomically and in order.
     *
     * Generated from protobuf field <code>repeated .google.firestore.v1.DocumentTransform.FieldTransform update_transforms = 7;</code>
     * @param \Google\Cloud\Firestore\V1\DocumentTransform\FieldTransform[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setUpdateTransforms($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Firestore\V1\DocumentTransform\FieldTransform::class);
        $this->update_transforms = $arr;

        return $this;
    }

    /**
     * An optional precondition on the document.
     * The write will fail if this is set and not met by the target document.
     *
     * Generated from protobuf field <code>.google.firestore.v1.Precondition current_document = 4;</code>
     * @return \Google\Cloud\Firestore\V1\Precondition|null
     */
    public function getCurrentDocument()
    {
        return $this->current_document;
    }

    public function hasCurrentDocument()
    {
        return isset($this->current_document);
    }

    public function clearCurrentDocument()
    {
        unset($this->current_document);
    }

    /**
     * An optional precondition on the document.
     * The write will fail if this is set and not met by the target document.
     *
     * Generated from protobuf field <code>.google.firestore.v1.Precondition current_document = 4;</code>
     * @param \Google\Cloud\Firestore\V1\Precondition $var
     * @return $this
     */
    public function setCurrentDocument($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Firestore\V1\Precondition::class);
        $this->current_document = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->whichOneof("operation");
    }

}

