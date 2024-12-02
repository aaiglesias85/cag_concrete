<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/retail/v2/product_service.proto

namespace Google\Cloud\Retail\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for
 * [ProductService.AddFulfillmentPlaces][google.cloud.retail.v2.ProductService.AddFulfillmentPlaces]
 * method.
 *
 * Generated from protobuf message <code>google.cloud.retail.v2.AddFulfillmentPlacesRequest</code>
 */
class AddFulfillmentPlacesRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Full resource name of [Product][google.cloud.retail.v2.Product],
     * such as
     * `projects/&#42;&#47;locations/global/catalogs/default_catalog/branches/default_branch/products/some_product_id`.
     * If the caller does not have permission to access the
     * [Product][google.cloud.retail.v2.Product], regardless of whether or not it
     * exists, a PERMISSION_DENIED error is returned.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $product = '';
    /**
     * Required. The fulfillment type, including commonly used types (such as
     * pickup in store and same day delivery), and custom types.
     * Supported values:
     * * "pickup-in-store"
     * * "ship-to-store"
     * * "same-day-delivery"
     * * "next-day-delivery"
     * * "custom-type-1"
     * * "custom-type-2"
     * * "custom-type-3"
     * * "custom-type-4"
     * * "custom-type-5"
     * If this field is set to an invalid value other than these, an
     * INVALID_ARGUMENT error is returned.
     * This field directly corresponds to [Product.fulfillment_info.type][].
     *
     * Generated from protobuf field <code>string type = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $type = '';
    /**
     * Required. The IDs for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type], such as
     * the store IDs for "pickup-in-store" or the region IDs for
     * "same-day-delivery" to be added for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type]. Duplicate
     * IDs will be automatically ignored.
     * At least 1 value is required, and a maximum of 2000 values are allowed.
     * Each value must be a string with a length limit of 10 characters, matching
     * the pattern `[a-zA-Z0-9_-]+`, such as "store1" or "REGION-2". Otherwise, an
     * INVALID_ARGUMENT error is returned.
     * If the total number of place IDs exceeds 2000 for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type] after
     * adding, then the update will be rejected.
     *
     * Generated from protobuf field <code>repeated string place_ids = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $place_ids;
    /**
     * The time when the fulfillment updates are issued, used to prevent
     * out-of-order updates on fulfillment information. If not provided, the
     * internal system time will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp add_time = 4;</code>
     */
    private $add_time = null;
    /**
     * If set to true, and the [Product][google.cloud.retail.v2.Product] is not
     * found, the fulfillment information will still be processed and retained for
     * at most 1 day and processed once the
     * [Product][google.cloud.retail.v2.Product] is created. If set to false, a
     * NOT_FOUND error is returned if the
     * [Product][google.cloud.retail.v2.Product] is not found.
     *
     * Generated from protobuf field <code>bool allow_missing = 5;</code>
     */
    private $allow_missing = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $product
     *           Required. Full resource name of [Product][google.cloud.retail.v2.Product],
     *           such as
     *           `projects/&#42;&#47;locations/global/catalogs/default_catalog/branches/default_branch/products/some_product_id`.
     *           If the caller does not have permission to access the
     *           [Product][google.cloud.retail.v2.Product], regardless of whether or not it
     *           exists, a PERMISSION_DENIED error is returned.
     *     @type string $type
     *           Required. The fulfillment type, including commonly used types (such as
     *           pickup in store and same day delivery), and custom types.
     *           Supported values:
     *           * "pickup-in-store"
     *           * "ship-to-store"
     *           * "same-day-delivery"
     *           * "next-day-delivery"
     *           * "custom-type-1"
     *           * "custom-type-2"
     *           * "custom-type-3"
     *           * "custom-type-4"
     *           * "custom-type-5"
     *           If this field is set to an invalid value other than these, an
     *           INVALID_ARGUMENT error is returned.
     *           This field directly corresponds to [Product.fulfillment_info.type][].
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $place_ids
     *           Required. The IDs for this
     *           [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type], such as
     *           the store IDs for "pickup-in-store" or the region IDs for
     *           "same-day-delivery" to be added for this
     *           [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type]. Duplicate
     *           IDs will be automatically ignored.
     *           At least 1 value is required, and a maximum of 2000 values are allowed.
     *           Each value must be a string with a length limit of 10 characters, matching
     *           the pattern `[a-zA-Z0-9_-]+`, such as "store1" or "REGION-2". Otherwise, an
     *           INVALID_ARGUMENT error is returned.
     *           If the total number of place IDs exceeds 2000 for this
     *           [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type] after
     *           adding, then the update will be rejected.
     *     @type \Google\Protobuf\Timestamp $add_time
     *           The time when the fulfillment updates are issued, used to prevent
     *           out-of-order updates on fulfillment information. If not provided, the
     *           internal system time will be used.
     *     @type bool $allow_missing
     *           If set to true, and the [Product][google.cloud.retail.v2.Product] is not
     *           found, the fulfillment information will still be processed and retained for
     *           at most 1 day and processed once the
     *           [Product][google.cloud.retail.v2.Product] is created. If set to false, a
     *           NOT_FOUND error is returned if the
     *           [Product][google.cloud.retail.v2.Product] is not found.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Retail\V2\ProductService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Full resource name of [Product][google.cloud.retail.v2.Product],
     * such as
     * `projects/&#42;&#47;locations/global/catalogs/default_catalog/branches/default_branch/products/some_product_id`.
     * If the caller does not have permission to access the
     * [Product][google.cloud.retail.v2.Product], regardless of whether or not it
     * exists, a PERMISSION_DENIED error is returned.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Required. Full resource name of [Product][google.cloud.retail.v2.Product],
     * such as
     * `projects/&#42;&#47;locations/global/catalogs/default_catalog/branches/default_branch/products/some_product_id`.
     * If the caller does not have permission to access the
     * [Product][google.cloud.retail.v2.Product], regardless of whether or not it
     * exists, a PERMISSION_DENIED error is returned.
     *
     * Generated from protobuf field <code>string product = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setProduct($var)
    {
        GPBUtil::checkString($var, True);
        $this->product = $var;

        return $this;
    }

    /**
     * Required. The fulfillment type, including commonly used types (such as
     * pickup in store and same day delivery), and custom types.
     * Supported values:
     * * "pickup-in-store"
     * * "ship-to-store"
     * * "same-day-delivery"
     * * "next-day-delivery"
     * * "custom-type-1"
     * * "custom-type-2"
     * * "custom-type-3"
     * * "custom-type-4"
     * * "custom-type-5"
     * If this field is set to an invalid value other than these, an
     * INVALID_ARGUMENT error is returned.
     * This field directly corresponds to [Product.fulfillment_info.type][].
     *
     * Generated from protobuf field <code>string type = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Required. The fulfillment type, including commonly used types (such as
     * pickup in store and same day delivery), and custom types.
     * Supported values:
     * * "pickup-in-store"
     * * "ship-to-store"
     * * "same-day-delivery"
     * * "next-day-delivery"
     * * "custom-type-1"
     * * "custom-type-2"
     * * "custom-type-3"
     * * "custom-type-4"
     * * "custom-type-5"
     * If this field is set to an invalid value other than these, an
     * INVALID_ARGUMENT error is returned.
     * This field directly corresponds to [Product.fulfillment_info.type][].
     *
     * Generated from protobuf field <code>string type = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkString($var, True);
        $this->type = $var;

        return $this;
    }

    /**
     * Required. The IDs for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type], such as
     * the store IDs for "pickup-in-store" or the region IDs for
     * "same-day-delivery" to be added for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type]. Duplicate
     * IDs will be automatically ignored.
     * At least 1 value is required, and a maximum of 2000 values are allowed.
     * Each value must be a string with a length limit of 10 characters, matching
     * the pattern `[a-zA-Z0-9_-]+`, such as "store1" or "REGION-2". Otherwise, an
     * INVALID_ARGUMENT error is returned.
     * If the total number of place IDs exceeds 2000 for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type] after
     * adding, then the update will be rejected.
     *
     * Generated from protobuf field <code>repeated string place_ids = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPlaceIds()
    {
        return $this->place_ids;
    }

    /**
     * Required. The IDs for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type], such as
     * the store IDs for "pickup-in-store" or the region IDs for
     * "same-day-delivery" to be added for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type]. Duplicate
     * IDs will be automatically ignored.
     * At least 1 value is required, and a maximum of 2000 values are allowed.
     * Each value must be a string with a length limit of 10 characters, matching
     * the pattern `[a-zA-Z0-9_-]+`, such as "store1" or "REGION-2". Otherwise, an
     * INVALID_ARGUMENT error is returned.
     * If the total number of place IDs exceeds 2000 for this
     * [type][google.cloud.retail.v2.AddFulfillmentPlacesRequest.type] after
     * adding, then the update will be rejected.
     *
     * Generated from protobuf field <code>repeated string place_ids = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPlaceIds($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->place_ids = $arr;

        return $this;
    }

    /**
     * The time when the fulfillment updates are issued, used to prevent
     * out-of-order updates on fulfillment information. If not provided, the
     * internal system time will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp add_time = 4;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getAddTime()
    {
        return $this->add_time;
    }

    public function hasAddTime()
    {
        return isset($this->add_time);
    }

    public function clearAddTime()
    {
        unset($this->add_time);
    }

    /**
     * The time when the fulfillment updates are issued, used to prevent
     * out-of-order updates on fulfillment information. If not provided, the
     * internal system time will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp add_time = 4;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setAddTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->add_time = $var;

        return $this;
    }

    /**
     * If set to true, and the [Product][google.cloud.retail.v2.Product] is not
     * found, the fulfillment information will still be processed and retained for
     * at most 1 day and processed once the
     * [Product][google.cloud.retail.v2.Product] is created. If set to false, a
     * NOT_FOUND error is returned if the
     * [Product][google.cloud.retail.v2.Product] is not found.
     *
     * Generated from protobuf field <code>bool allow_missing = 5;</code>
     * @return bool
     */
    public function getAllowMissing()
    {
        return $this->allow_missing;
    }

    /**
     * If set to true, and the [Product][google.cloud.retail.v2.Product] is not
     * found, the fulfillment information will still be processed and retained for
     * at most 1 day and processed once the
     * [Product][google.cloud.retail.v2.Product] is created. If set to false, a
     * NOT_FOUND error is returned if the
     * [Product][google.cloud.retail.v2.Product] is not found.
     *
     * Generated from protobuf field <code>bool allow_missing = 5;</code>
     * @param bool $var
     * @return $this
     */
    public function setAllowMissing($var)
    {
        GPBUtil::checkBool($var);
        $this->allow_missing = $var;

        return $this;
    }

}

