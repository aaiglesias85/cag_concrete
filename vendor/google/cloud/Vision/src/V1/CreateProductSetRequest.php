<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vision/v1/product_search_service.proto

namespace Google\Cloud\Vision\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for the `CreateProductSet` method.
 *
 * Generated from protobuf message <code>google.cloud.vision.v1.CreateProductSetRequest</code>
 */
class CreateProductSetRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The project in which the ProductSet should be created.
     * Format is `projects/PROJECT_ID/locations/LOC_ID`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The ProductSet to create.
     *
     * Generated from protobuf field <code>.google.cloud.vision.v1.ProductSet product_set = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $product_set = null;
    /**
     * A user-supplied resource id for this ProductSet. If set, the server will
     * attempt to use this value as the resource id. If it is already in use, an
     * error is returned with code ALREADY_EXISTS. Must be at most 128 characters
     * long. It cannot contain the character `/`.
     *
     * Generated from protobuf field <code>string product_set_id = 3;</code>
     */
    private $product_set_id = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The project in which the ProductSet should be created.
     *           Format is `projects/PROJECT_ID/locations/LOC_ID`.
     *     @type \Google\Cloud\Vision\V1\ProductSet $product_set
     *           Required. The ProductSet to create.
     *     @type string $product_set_id
     *           A user-supplied resource id for this ProductSet. If set, the server will
     *           attempt to use this value as the resource id. If it is already in use, an
     *           error is returned with code ALREADY_EXISTS. Must be at most 128 characters
     *           long. It cannot contain the character `/`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Vision\V1\ProductSearchService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The project in which the ProductSet should be created.
     * Format is `projects/PROJECT_ID/locations/LOC_ID`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The project in which the ProductSet should be created.
     * Format is `projects/PROJECT_ID/locations/LOC_ID`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. The ProductSet to create.
     *
     * Generated from protobuf field <code>.google.cloud.vision.v1.ProductSet product_set = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Vision\V1\ProductSet|null
     */
    public function getProductSet()
    {
        return $this->product_set;
    }

    public function hasProductSet()
    {
        return isset($this->product_set);
    }

    public function clearProductSet()
    {
        unset($this->product_set);
    }

    /**
     * Required. The ProductSet to create.
     *
     * Generated from protobuf field <code>.google.cloud.vision.v1.ProductSet product_set = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Vision\V1\ProductSet $var
     * @return $this
     */
    public function setProductSet($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Vision\V1\ProductSet::class);
        $this->product_set = $var;

        return $this;
    }

    /**
     * A user-supplied resource id for this ProductSet. If set, the server will
     * attempt to use this value as the resource id. If it is already in use, an
     * error is returned with code ALREADY_EXISTS. Must be at most 128 characters
     * long. It cannot contain the character `/`.
     *
     * Generated from protobuf field <code>string product_set_id = 3;</code>
     * @return string
     */
    public function getProductSetId()
    {
        return $this->product_set_id;
    }

    /**
     * A user-supplied resource id for this ProductSet. If set, the server will
     * attempt to use this value as the resource id. If it is already in use, an
     * error is returned with code ALREADY_EXISTS. Must be at most 128 characters
     * long. It cannot contain the character `/`.
     *
     * Generated from protobuf field <code>string product_set_id = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setProductSetId($var)
    {
        GPBUtil::checkString($var, True);
        $this->product_set_id = $var;

        return $this;
    }

}

