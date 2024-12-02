<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/channel/v1/service.proto

namespace Google\Cloud\Channel\V1\ListPurchasableOffersRequest;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * List Offers for CreateEntitlement purchase.
 *
 * Generated from protobuf message <code>google.cloud.channel.v1.ListPurchasableOffersRequest.CreateEntitlementPurchase</code>
 */
class CreateEntitlementPurchase extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. SKU that the result should be restricted to.
     * Format: products/{product_id}/skus/{sku_id}.
     *
     * Generated from protobuf field <code>string sku = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $sku = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $sku
     *           Required. SKU that the result should be restricted to.
     *           Format: products/{product_id}/skus/{sku_id}.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Channel\V1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. SKU that the result should be restricted to.
     * Format: products/{product_id}/skus/{sku_id}.
     *
     * Generated from protobuf field <code>string sku = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Required. SKU that the result should be restricted to.
     * Format: products/{product_id}/skus/{sku_id}.
     *
     * Generated from protobuf field <code>string sku = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setSku($var)
    {
        GPBUtil::checkString($var, True);
        $this->sku = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CreateEntitlementPurchase::class, \Google\Cloud\Channel\V1\ListPurchasableOffersRequest_CreateEntitlementPurchase::class);

