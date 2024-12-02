<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/video/stitcher/v1/companions.proto

namespace Google\Cloud\Video\Stitcher\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata for a companion.
 *
 * Generated from protobuf message <code>google.cloud.video.stitcher.v1.Companion</code>
 */
class Companion extends \Google\Protobuf\Internal\Message
{
    /**
     * The API necessary to communicate with the creative if available.
     *
     * Generated from protobuf field <code>string api_framework = 1;</code>
     */
    private $api_framework = '';
    /**
     * The pixel height of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 height_px = 2;</code>
     */
    private $height_px = 0;
    /**
     * The pixel width of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 width_px = 3;</code>
     */
    private $width_px = 0;
    /**
     * The pixel height of the creative.
     *
     * Generated from protobuf field <code>int32 asset_height_px = 4;</code>
     */
    private $asset_height_px = 0;
    /**
     * The maximum pixel height of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_height_px = 5;</code>
     */
    private $expanded_height_px = 0;
    /**
     * The pixel width of the creative.
     *
     * Generated from protobuf field <code>int32 asset_width_px = 6;</code>
     */
    private $asset_width_px = 0;
    /**
     * The maximum pixel width of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_width_px = 7;</code>
     */
    private $expanded_width_px = 0;
    /**
     * The ID used to identify the desired placement on a publisher's page.
     * Values to be used should be discussed between publishers and
     * advertisers.
     *
     * Generated from protobuf field <code>string ad_slot_id = 8;</code>
     */
    private $ad_slot_id = '';
    /**
     * The list of tracking events for the companion.
     *
     * Generated from protobuf field <code>repeated .google.cloud.video.stitcher.v1.Event events = 9;</code>
     */
    private $events;
    protected $ad_resource;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Video\Stitcher\V1\IframeAdResource $iframe_ad_resource
     *           The IFrame ad resource associated with the companion ad.
     *     @type \Google\Cloud\Video\Stitcher\V1\StaticAdResource $static_ad_resource
     *           The static ad resource associated with the companion ad.
     *     @type \Google\Cloud\Video\Stitcher\V1\HtmlAdResource $html_ad_resource
     *           The HTML ad resource associated with the companion ad.
     *     @type string $api_framework
     *           The API necessary to communicate with the creative if available.
     *     @type int $height_px
     *           The pixel height of the placement slot for the intended creative.
     *     @type int $width_px
     *           The pixel width of the placement slot for the intended creative.
     *     @type int $asset_height_px
     *           The pixel height of the creative.
     *     @type int $expanded_height_px
     *           The maximum pixel height of the creative in its expanded state.
     *     @type int $asset_width_px
     *           The pixel width of the creative.
     *     @type int $expanded_width_px
     *           The maximum pixel width of the creative in its expanded state.
     *     @type string $ad_slot_id
     *           The ID used to identify the desired placement on a publisher's page.
     *           Values to be used should be discussed between publishers and
     *           advertisers.
     *     @type \Google\Cloud\Video\Stitcher\V1\Event[]|\Google\Protobuf\Internal\RepeatedField $events
     *           The list of tracking events for the companion.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Video\Stitcher\V1\Companions::initOnce();
        parent::__construct($data);
    }

    /**
     * The IFrame ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.IframeAdResource iframe_ad_resource = 10;</code>
     * @return \Google\Cloud\Video\Stitcher\V1\IframeAdResource|null
     */
    public function getIframeAdResource()
    {
        return $this->readOneof(10);
    }

    public function hasIframeAdResource()
    {
        return $this->hasOneof(10);
    }

    /**
     * The IFrame ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.IframeAdResource iframe_ad_resource = 10;</code>
     * @param \Google\Cloud\Video\Stitcher\V1\IframeAdResource $var
     * @return $this
     */
    public function setIframeAdResource($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Video\Stitcher\V1\IframeAdResource::class);
        $this->writeOneof(10, $var);

        return $this;
    }

    /**
     * The static ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.StaticAdResource static_ad_resource = 11;</code>
     * @return \Google\Cloud\Video\Stitcher\V1\StaticAdResource|null
     */
    public function getStaticAdResource()
    {
        return $this->readOneof(11);
    }

    public function hasStaticAdResource()
    {
        return $this->hasOneof(11);
    }

    /**
     * The static ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.StaticAdResource static_ad_resource = 11;</code>
     * @param \Google\Cloud\Video\Stitcher\V1\StaticAdResource $var
     * @return $this
     */
    public function setStaticAdResource($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Video\Stitcher\V1\StaticAdResource::class);
        $this->writeOneof(11, $var);

        return $this;
    }

    /**
     * The HTML ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.HtmlAdResource html_ad_resource = 12;</code>
     * @return \Google\Cloud\Video\Stitcher\V1\HtmlAdResource|null
     */
    public function getHtmlAdResource()
    {
        return $this->readOneof(12);
    }

    public function hasHtmlAdResource()
    {
        return $this->hasOneof(12);
    }

    /**
     * The HTML ad resource associated with the companion ad.
     *
     * Generated from protobuf field <code>.google.cloud.video.stitcher.v1.HtmlAdResource html_ad_resource = 12;</code>
     * @param \Google\Cloud\Video\Stitcher\V1\HtmlAdResource $var
     * @return $this
     */
    public function setHtmlAdResource($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Video\Stitcher\V1\HtmlAdResource::class);
        $this->writeOneof(12, $var);

        return $this;
    }

    /**
     * The API necessary to communicate with the creative if available.
     *
     * Generated from protobuf field <code>string api_framework = 1;</code>
     * @return string
     */
    public function getApiFramework()
    {
        return $this->api_framework;
    }

    /**
     * The API necessary to communicate with the creative if available.
     *
     * Generated from protobuf field <code>string api_framework = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setApiFramework($var)
    {
        GPBUtil::checkString($var, True);
        $this->api_framework = $var;

        return $this;
    }

    /**
     * The pixel height of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 height_px = 2;</code>
     * @return int
     */
    public function getHeightPx()
    {
        return $this->height_px;
    }

    /**
     * The pixel height of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 height_px = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setHeightPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->height_px = $var;

        return $this;
    }

    /**
     * The pixel width of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 width_px = 3;</code>
     * @return int
     */
    public function getWidthPx()
    {
        return $this->width_px;
    }

    /**
     * The pixel width of the placement slot for the intended creative.
     *
     * Generated from protobuf field <code>int32 width_px = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setWidthPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->width_px = $var;

        return $this;
    }

    /**
     * The pixel height of the creative.
     *
     * Generated from protobuf field <code>int32 asset_height_px = 4;</code>
     * @return int
     */
    public function getAssetHeightPx()
    {
        return $this->asset_height_px;
    }

    /**
     * The pixel height of the creative.
     *
     * Generated from protobuf field <code>int32 asset_height_px = 4;</code>
     * @param int $var
     * @return $this
     */
    public function setAssetHeightPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->asset_height_px = $var;

        return $this;
    }

    /**
     * The maximum pixel height of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_height_px = 5;</code>
     * @return int
     */
    public function getExpandedHeightPx()
    {
        return $this->expanded_height_px;
    }

    /**
     * The maximum pixel height of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_height_px = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setExpandedHeightPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->expanded_height_px = $var;

        return $this;
    }

    /**
     * The pixel width of the creative.
     *
     * Generated from protobuf field <code>int32 asset_width_px = 6;</code>
     * @return int
     */
    public function getAssetWidthPx()
    {
        return $this->asset_width_px;
    }

    /**
     * The pixel width of the creative.
     *
     * Generated from protobuf field <code>int32 asset_width_px = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setAssetWidthPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->asset_width_px = $var;

        return $this;
    }

    /**
     * The maximum pixel width of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_width_px = 7;</code>
     * @return int
     */
    public function getExpandedWidthPx()
    {
        return $this->expanded_width_px;
    }

    /**
     * The maximum pixel width of the creative in its expanded state.
     *
     * Generated from protobuf field <code>int32 expanded_width_px = 7;</code>
     * @param int $var
     * @return $this
     */
    public function setExpandedWidthPx($var)
    {
        GPBUtil::checkInt32($var);
        $this->expanded_width_px = $var;

        return $this;
    }

    /**
     * The ID used to identify the desired placement on a publisher's page.
     * Values to be used should be discussed between publishers and
     * advertisers.
     *
     * Generated from protobuf field <code>string ad_slot_id = 8;</code>
     * @return string
     */
    public function getAdSlotId()
    {
        return $this->ad_slot_id;
    }

    /**
     * The ID used to identify the desired placement on a publisher's page.
     * Values to be used should be discussed between publishers and
     * advertisers.
     *
     * Generated from protobuf field <code>string ad_slot_id = 8;</code>
     * @param string $var
     * @return $this
     */
    public function setAdSlotId($var)
    {
        GPBUtil::checkString($var, True);
        $this->ad_slot_id = $var;

        return $this;
    }

    /**
     * The list of tracking events for the companion.
     *
     * Generated from protobuf field <code>repeated .google.cloud.video.stitcher.v1.Event events = 9;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * The list of tracking events for the companion.
     *
     * Generated from protobuf field <code>repeated .google.cloud.video.stitcher.v1.Event events = 9;</code>
     * @param \Google\Cloud\Video\Stitcher\V1\Event[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setEvents($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Video\Stitcher\V1\Event::class);
        $this->events = $arr;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdResource()
    {
        return $this->whichOneof("ad_resource");
    }

}

