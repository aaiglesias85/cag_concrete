<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/retail/v2/search_service.proto

namespace Google\Cloud\Retail\V2\SearchRequest\FacetSpec;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Specifies how a facet is computed.
 *
 * Generated from protobuf message <code>google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey</code>
 */
class FacetKey extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Supported textual and numerical facet keys in
     * [Product][google.cloud.retail.v2.Product] object, over which the facet
     * values are computed. Facet key is case-sensitive.
     * Allowed facet keys when
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is not specified:
     * * textual_field =
     *     * "brands"
     *     * "categories"
     *     * "genders"
     *     * "ageGroups"
     *     * "availability"
     *     * "colorFamilies"
     *     * "colors"
     *     * "sizes"
     *     * "materials"
     *     * "patterns"
     *     * "conditions"
     *     * "attributes.key"
     *     * "pickupInStore"
     *     * "shipToStore"
     *     * "sameDayDelivery"
     *     * "nextDayDelivery"
     *     * "customFulfillment1"
     *     * "customFulfillment2"
     *     * "customFulfillment3"
     *     * "customFulfillment4"
     *     * "customFulfillment5"
     *     * "inventory(place_id,attributes.key)"
     * * numerical_field =
     *     * "price"
     *     * "discount"
     *     * "rating"
     *     * "ratingCount"
     *     * "attributes.key"
     *     * "inventory(place_id,price)"
     *     * "inventory(place_id,original_price)"
     *     * "inventory(place_id,attributes.key)"
     *
     * Generated from protobuf field <code>string key = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $key = '';
    /**
     * Set only if values should be bucketized into intervals. Must be set
     * for facets with numerical values. Must not be set for facet with text
     * values. Maximum number of intervals is 30.
     *
     * Generated from protobuf field <code>repeated .google.cloud.retail.v2.Interval intervals = 2;</code>
     */
    private $intervals;
    /**
     * Only get facet for the given restricted values. For example, when using
     * "pickupInStore" as key and set restricted values to
     * ["store123", "store456"], only facets for "store123" and "store456" are
     * returned. Only supported on predefined textual fields, custom textual
     * attributes and fulfillments. Maximum is 20.
     * Must be set for the fulfillment facet keys:
     * * pickupInStore
     * * shipToStore
     * * sameDayDelivery
     * * nextDayDelivery
     * * customFulfillment1
     * * customFulfillment2
     * * customFulfillment3
     * * customFulfillment4
     * * customFulfillment5
     *
     * Generated from protobuf field <code>repeated string restricted_values = 3;</code>
     */
    private $restricted_values;
    /**
     * Only get facet values that start with the given string prefix. For
     * example, suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "prefixes" to "Women", the
     * "categories" facet will give only "Women > Shoe" and "Women > Dress".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string prefixes = 8;</code>
     */
    private $prefixes;
    /**
     * Only get facet values that contains the given strings. For example,
     * suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "contains" to "Shoe", the
     * "categories" facet will give only "Women > Shoe" and "Men > Shoe".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string contains = 9;</code>
     */
    private $contains;
    /**
     * The order in which [Facet.values][] are returned.
     * Allowed values are:
     * * "count desc", which means order by [Facet.FacetValue.count][]
     * descending.
     * * "value desc", which means order by [Facet.FacetValue.value][]
     * descending.
     *   Only applies to textual facets.
     * If not set, textual values are sorted in [natural
     * order](https://en.wikipedia.org/wiki/Natural_sort_order); numerical
     * intervals are sorted in the order given by
     * [FacetSpec.FacetKey.intervals][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.intervals];
     * [FulfillmentInfo.place_ids][google.cloud.retail.v2.FulfillmentInfo.place_ids]
     * are sorted in the order given by
     * [FacetSpec.FacetKey.restricted_values][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.restricted_values].
     *
     * Generated from protobuf field <code>string order_by = 4;</code>
     */
    private $order_by = '';
    /**
     * The query that is used to compute facet for the given facet key.
     * When provided, it will override the default behavior of facet
     * computation. The query syntax is the same as a filter expression. See
     * [SearchRequest.filter][google.cloud.retail.v2.SearchRequest.filter] for
     * detail syntax and limitations. Notice that there is no limitation on
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * when query is specified.
     * In the response, [FacetValue.value][] will be always "1" and
     * [FacetValue.count][] will be the number of results that matches the
     * query.
     * For example, you can set a customized facet for "shipToStore",
     * where
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * is "customizedShipToStore", and
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is "availability: ANY(\"IN_STOCK\") AND shipToStore: ANY(\"123\")".
     * Then the facet will count the products that are both in stock and ship
     * to store "123".
     *
     * Generated from protobuf field <code>string query = 5;</code>
     */
    private $query = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $key
     *           Required. Supported textual and numerical facet keys in
     *           [Product][google.cloud.retail.v2.Product] object, over which the facet
     *           values are computed. Facet key is case-sensitive.
     *           Allowed facet keys when
     *           [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     *           is not specified:
     *           * textual_field =
     *               * "brands"
     *               * "categories"
     *               * "genders"
     *               * "ageGroups"
     *               * "availability"
     *               * "colorFamilies"
     *               * "colors"
     *               * "sizes"
     *               * "materials"
     *               * "patterns"
     *               * "conditions"
     *               * "attributes.key"
     *               * "pickupInStore"
     *               * "shipToStore"
     *               * "sameDayDelivery"
     *               * "nextDayDelivery"
     *               * "customFulfillment1"
     *               * "customFulfillment2"
     *               * "customFulfillment3"
     *               * "customFulfillment4"
     *               * "customFulfillment5"
     *               * "inventory(place_id,attributes.key)"
     *           * numerical_field =
     *               * "price"
     *               * "discount"
     *               * "rating"
     *               * "ratingCount"
     *               * "attributes.key"
     *               * "inventory(place_id,price)"
     *               * "inventory(place_id,original_price)"
     *               * "inventory(place_id,attributes.key)"
     *     @type \Google\Cloud\Retail\V2\Interval[]|\Google\Protobuf\Internal\RepeatedField $intervals
     *           Set only if values should be bucketized into intervals. Must be set
     *           for facets with numerical values. Must not be set for facet with text
     *           values. Maximum number of intervals is 30.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $restricted_values
     *           Only get facet for the given restricted values. For example, when using
     *           "pickupInStore" as key and set restricted values to
     *           ["store123", "store456"], only facets for "store123" and "store456" are
     *           returned. Only supported on predefined textual fields, custom textual
     *           attributes and fulfillments. Maximum is 20.
     *           Must be set for the fulfillment facet keys:
     *           * pickupInStore
     *           * shipToStore
     *           * sameDayDelivery
     *           * nextDayDelivery
     *           * customFulfillment1
     *           * customFulfillment2
     *           * customFulfillment3
     *           * customFulfillment4
     *           * customFulfillment5
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $prefixes
     *           Only get facet values that start with the given string prefix. For
     *           example, suppose "categories" has three values "Women > Shoe",
     *           "Women > Dress" and "Men > Shoe". If set "prefixes" to "Women", the
     *           "categories" facet will give only "Women > Shoe" and "Women > Dress".
     *           Only supported on textual fields. Maximum is 10.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $contains
     *           Only get facet values that contains the given strings. For example,
     *           suppose "categories" has three values "Women > Shoe",
     *           "Women > Dress" and "Men > Shoe". If set "contains" to "Shoe", the
     *           "categories" facet will give only "Women > Shoe" and "Men > Shoe".
     *           Only supported on textual fields. Maximum is 10.
     *     @type string $order_by
     *           The order in which [Facet.values][] are returned.
     *           Allowed values are:
     *           * "count desc", which means order by [Facet.FacetValue.count][]
     *           descending.
     *           * "value desc", which means order by [Facet.FacetValue.value][]
     *           descending.
     *             Only applies to textual facets.
     *           If not set, textual values are sorted in [natural
     *           order](https://en.wikipedia.org/wiki/Natural_sort_order); numerical
     *           intervals are sorted in the order given by
     *           [FacetSpec.FacetKey.intervals][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.intervals];
     *           [FulfillmentInfo.place_ids][google.cloud.retail.v2.FulfillmentInfo.place_ids]
     *           are sorted in the order given by
     *           [FacetSpec.FacetKey.restricted_values][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.restricted_values].
     *     @type string $query
     *           The query that is used to compute facet for the given facet key.
     *           When provided, it will override the default behavior of facet
     *           computation. The query syntax is the same as a filter expression. See
     *           [SearchRequest.filter][google.cloud.retail.v2.SearchRequest.filter] for
     *           detail syntax and limitations. Notice that there is no limitation on
     *           [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     *           when query is specified.
     *           In the response, [FacetValue.value][] will be always "1" and
     *           [FacetValue.count][] will be the number of results that matches the
     *           query.
     *           For example, you can set a customized facet for "shipToStore",
     *           where
     *           [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     *           is "customizedShipToStore", and
     *           [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     *           is "availability: ANY(\"IN_STOCK\") AND shipToStore: ANY(\"123\")".
     *           Then the facet will count the products that are both in stock and ship
     *           to store "123".
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Retail\V2\SearchService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Supported textual and numerical facet keys in
     * [Product][google.cloud.retail.v2.Product] object, over which the facet
     * values are computed. Facet key is case-sensitive.
     * Allowed facet keys when
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is not specified:
     * * textual_field =
     *     * "brands"
     *     * "categories"
     *     * "genders"
     *     * "ageGroups"
     *     * "availability"
     *     * "colorFamilies"
     *     * "colors"
     *     * "sizes"
     *     * "materials"
     *     * "patterns"
     *     * "conditions"
     *     * "attributes.key"
     *     * "pickupInStore"
     *     * "shipToStore"
     *     * "sameDayDelivery"
     *     * "nextDayDelivery"
     *     * "customFulfillment1"
     *     * "customFulfillment2"
     *     * "customFulfillment3"
     *     * "customFulfillment4"
     *     * "customFulfillment5"
     *     * "inventory(place_id,attributes.key)"
     * * numerical_field =
     *     * "price"
     *     * "discount"
     *     * "rating"
     *     * "ratingCount"
     *     * "attributes.key"
     *     * "inventory(place_id,price)"
     *     * "inventory(place_id,original_price)"
     *     * "inventory(place_id,attributes.key)"
     *
     * Generated from protobuf field <code>string key = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Required. Supported textual and numerical facet keys in
     * [Product][google.cloud.retail.v2.Product] object, over which the facet
     * values are computed. Facet key is case-sensitive.
     * Allowed facet keys when
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is not specified:
     * * textual_field =
     *     * "brands"
     *     * "categories"
     *     * "genders"
     *     * "ageGroups"
     *     * "availability"
     *     * "colorFamilies"
     *     * "colors"
     *     * "sizes"
     *     * "materials"
     *     * "patterns"
     *     * "conditions"
     *     * "attributes.key"
     *     * "pickupInStore"
     *     * "shipToStore"
     *     * "sameDayDelivery"
     *     * "nextDayDelivery"
     *     * "customFulfillment1"
     *     * "customFulfillment2"
     *     * "customFulfillment3"
     *     * "customFulfillment4"
     *     * "customFulfillment5"
     *     * "inventory(place_id,attributes.key)"
     * * numerical_field =
     *     * "price"
     *     * "discount"
     *     * "rating"
     *     * "ratingCount"
     *     * "attributes.key"
     *     * "inventory(place_id,price)"
     *     * "inventory(place_id,original_price)"
     *     * "inventory(place_id,attributes.key)"
     *
     * Generated from protobuf field <code>string key = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setKey($var)
    {
        GPBUtil::checkString($var, True);
        $this->key = $var;

        return $this;
    }

    /**
     * Set only if values should be bucketized into intervals. Must be set
     * for facets with numerical values. Must not be set for facet with text
     * values. Maximum number of intervals is 30.
     *
     * Generated from protobuf field <code>repeated .google.cloud.retail.v2.Interval intervals = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getIntervals()
    {
        return $this->intervals;
    }

    /**
     * Set only if values should be bucketized into intervals. Must be set
     * for facets with numerical values. Must not be set for facet with text
     * values. Maximum number of intervals is 30.
     *
     * Generated from protobuf field <code>repeated .google.cloud.retail.v2.Interval intervals = 2;</code>
     * @param \Google\Cloud\Retail\V2\Interval[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setIntervals($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Retail\V2\Interval::class);
        $this->intervals = $arr;

        return $this;
    }

    /**
     * Only get facet for the given restricted values. For example, when using
     * "pickupInStore" as key and set restricted values to
     * ["store123", "store456"], only facets for "store123" and "store456" are
     * returned. Only supported on predefined textual fields, custom textual
     * attributes and fulfillments. Maximum is 20.
     * Must be set for the fulfillment facet keys:
     * * pickupInStore
     * * shipToStore
     * * sameDayDelivery
     * * nextDayDelivery
     * * customFulfillment1
     * * customFulfillment2
     * * customFulfillment3
     * * customFulfillment4
     * * customFulfillment5
     *
     * Generated from protobuf field <code>repeated string restricted_values = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRestrictedValues()
    {
        return $this->restricted_values;
    }

    /**
     * Only get facet for the given restricted values. For example, when using
     * "pickupInStore" as key and set restricted values to
     * ["store123", "store456"], only facets for "store123" and "store456" are
     * returned. Only supported on predefined textual fields, custom textual
     * attributes and fulfillments. Maximum is 20.
     * Must be set for the fulfillment facet keys:
     * * pickupInStore
     * * shipToStore
     * * sameDayDelivery
     * * nextDayDelivery
     * * customFulfillment1
     * * customFulfillment2
     * * customFulfillment3
     * * customFulfillment4
     * * customFulfillment5
     *
     * Generated from protobuf field <code>repeated string restricted_values = 3;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRestrictedValues($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->restricted_values = $arr;

        return $this;
    }

    /**
     * Only get facet values that start with the given string prefix. For
     * example, suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "prefixes" to "Women", the
     * "categories" facet will give only "Women > Shoe" and "Women > Dress".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string prefixes = 8;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Only get facet values that start with the given string prefix. For
     * example, suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "prefixes" to "Women", the
     * "categories" facet will give only "Women > Shoe" and "Women > Dress".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string prefixes = 8;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPrefixes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->prefixes = $arr;

        return $this;
    }

    /**
     * Only get facet values that contains the given strings. For example,
     * suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "contains" to "Shoe", the
     * "categories" facet will give only "Women > Shoe" and "Men > Shoe".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string contains = 9;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getContains()
    {
        return $this->contains;
    }

    /**
     * Only get facet values that contains the given strings. For example,
     * suppose "categories" has three values "Women > Shoe",
     * "Women > Dress" and "Men > Shoe". If set "contains" to "Shoe", the
     * "categories" facet will give only "Women > Shoe" and "Men > Shoe".
     * Only supported on textual fields. Maximum is 10.
     *
     * Generated from protobuf field <code>repeated string contains = 9;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setContains($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->contains = $arr;

        return $this;
    }

    /**
     * The order in which [Facet.values][] are returned.
     * Allowed values are:
     * * "count desc", which means order by [Facet.FacetValue.count][]
     * descending.
     * * "value desc", which means order by [Facet.FacetValue.value][]
     * descending.
     *   Only applies to textual facets.
     * If not set, textual values are sorted in [natural
     * order](https://en.wikipedia.org/wiki/Natural_sort_order); numerical
     * intervals are sorted in the order given by
     * [FacetSpec.FacetKey.intervals][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.intervals];
     * [FulfillmentInfo.place_ids][google.cloud.retail.v2.FulfillmentInfo.place_ids]
     * are sorted in the order given by
     * [FacetSpec.FacetKey.restricted_values][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.restricted_values].
     *
     * Generated from protobuf field <code>string order_by = 4;</code>
     * @return string
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * The order in which [Facet.values][] are returned.
     * Allowed values are:
     * * "count desc", which means order by [Facet.FacetValue.count][]
     * descending.
     * * "value desc", which means order by [Facet.FacetValue.value][]
     * descending.
     *   Only applies to textual facets.
     * If not set, textual values are sorted in [natural
     * order](https://en.wikipedia.org/wiki/Natural_sort_order); numerical
     * intervals are sorted in the order given by
     * [FacetSpec.FacetKey.intervals][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.intervals];
     * [FulfillmentInfo.place_ids][google.cloud.retail.v2.FulfillmentInfo.place_ids]
     * are sorted in the order given by
     * [FacetSpec.FacetKey.restricted_values][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.restricted_values].
     *
     * Generated from protobuf field <code>string order_by = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setOrderBy($var)
    {
        GPBUtil::checkString($var, True);
        $this->order_by = $var;

        return $this;
    }

    /**
     * The query that is used to compute facet for the given facet key.
     * When provided, it will override the default behavior of facet
     * computation. The query syntax is the same as a filter expression. See
     * [SearchRequest.filter][google.cloud.retail.v2.SearchRequest.filter] for
     * detail syntax and limitations. Notice that there is no limitation on
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * when query is specified.
     * In the response, [FacetValue.value][] will be always "1" and
     * [FacetValue.count][] will be the number of results that matches the
     * query.
     * For example, you can set a customized facet for "shipToStore",
     * where
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * is "customizedShipToStore", and
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is "availability: ANY(\"IN_STOCK\") AND shipToStore: ANY(\"123\")".
     * Then the facet will count the products that are both in stock and ship
     * to store "123".
     *
     * Generated from protobuf field <code>string query = 5;</code>
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * The query that is used to compute facet for the given facet key.
     * When provided, it will override the default behavior of facet
     * computation. The query syntax is the same as a filter expression. See
     * [SearchRequest.filter][google.cloud.retail.v2.SearchRequest.filter] for
     * detail syntax and limitations. Notice that there is no limitation on
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * when query is specified.
     * In the response, [FacetValue.value][] will be always "1" and
     * [FacetValue.count][] will be the number of results that matches the
     * query.
     * For example, you can set a customized facet for "shipToStore",
     * where
     * [FacetKey.key][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.key]
     * is "customizedShipToStore", and
     * [FacetKey.query][google.cloud.retail.v2.SearchRequest.FacetSpec.FacetKey.query]
     * is "availability: ANY(\"IN_STOCK\") AND shipToStore: ANY(\"123\")".
     * Then the facet will count the products that are both in stock and ship
     * to store "123".
     *
     * Generated from protobuf field <code>string query = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setQuery($var)
    {
        GPBUtil::checkString($var, True);
        $this->query = $var;

        return $this;
    }

}


