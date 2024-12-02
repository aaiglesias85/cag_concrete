<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/channel/v1/service.proto

namespace Google\Cloud\Channel\V1\ListPurchasableSkusRequest;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * List SKUs for a new entitlement. Make the purchase using
 * [CloudChannelService.CreateEntitlement][google.cloud.channel.v1.CloudChannelService.CreateEntitlement].
 *
 * Generated from protobuf message <code>google.cloud.channel.v1.ListPurchasableSkusRequest.CreateEntitlementPurchase</code>
 */
class CreateEntitlementPurchase extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. List SKUs belonging to this Product.
     * Format: products/{product_id}.
     * Supports products/- to retrieve SKUs for all products.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $product = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $product
     *           Required. List SKUs belonging to this Product.
     *           Format: products/{product_id}.
     *           Supports products/- to retrieve SKUs for all products.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Channel\V1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. List SKUs belonging to this Product.
     * Format: products/{product_id}.
     * Supports products/- to retrieve SKUs for all products.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Required. List SKUs belonging to this Product.
     * Format: products/{product_id}.
     * Supports products/- to retrieve SKUs for all products.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setProduct($var)
    {
        GPBUtil::checkString($var, True);
        $this->product = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CreateEntitlementPurchase::class, \Google\Cloud\Channel\V1\ListPurchasableSkusRequest_CreateEntitlementPurchase::class);

