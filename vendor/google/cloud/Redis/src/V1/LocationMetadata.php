<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/redis/v1/cloud_redis.proto

namespace Google\Cloud\Redis\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * This location metadata represents additional configuration options for a
 * given location where a Redis instance may be created. All fields are output
 * only. It is returned as content of the
 * `google.cloud.location.Location.metadata` field.
 *
 * Generated from protobuf message <code>google.cloud.redis.v1.LocationMetadata</code>
 */
class LocationMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. The set of available zones in the location. The map is keyed
     * by the lowercase ID of each zone, as defined by GCE. These keys can be
     * specified in `location_id` or `alternative_location_id` fields when
     * creating a Redis instance.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.redis.v1.ZoneMetadata> available_zones = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $available_zones;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $available_zones
     *           Output only. The set of available zones in the location. The map is keyed
     *           by the lowercase ID of each zone, as defined by GCE. These keys can be
     *           specified in `location_id` or `alternative_location_id` fields when
     *           creating a Redis instance.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Redis\V1\CloudRedis::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. The set of available zones in the location. The map is keyed
     * by the lowercase ID of each zone, as defined by GCE. These keys can be
     * specified in `location_id` or `alternative_location_id` fields when
     * creating a Redis instance.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.redis.v1.ZoneMetadata> available_zones = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getAvailableZones()
    {
        return $this->available_zones;
    }

    /**
     * Output only. The set of available zones in the location. The map is keyed
     * by the lowercase ID of each zone, as defined by GCE. These keys can be
     * specified in `location_id` or `alternative_location_id` fields when
     * creating a Redis instance.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.redis.v1.ZoneMetadata> available_zones = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setAvailableZones($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Redis\V1\ZoneMetadata::class);
        $this->available_zones = $arr;

        return $this;
    }

}

