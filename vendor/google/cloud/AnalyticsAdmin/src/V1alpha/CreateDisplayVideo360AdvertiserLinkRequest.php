<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/analytics_admin.proto

namespace Google\Analytics\Admin\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for CreateDisplayVideo360AdvertiserLink RPC.
 *
 * Generated from protobuf message <code>google.analytics.admin.v1alpha.CreateDisplayVideo360AdvertiserLinkRequest</code>
 */
class CreateDisplayVideo360AdvertiserLinkRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Example format: properties/1234
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The DisplayVideo360AdvertiserLink to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.DisplayVideo360AdvertiserLink display_video_360_advertiser_link = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $display_video_360_advertiser_link = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. Example format: properties/1234
     *     @type \Google\Analytics\Admin\V1alpha\DisplayVideo360AdvertiserLink $display_video_360_advertiser_link
     *           Required. The DisplayVideo360AdvertiserLink to create.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Admin\V1Alpha\AnalyticsAdmin::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Example format: properties/1234
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. Example format: properties/1234
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
     * Required. The DisplayVideo360AdvertiserLink to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.DisplayVideo360AdvertiserLink display_video_360_advertiser_link = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Analytics\Admin\V1alpha\DisplayVideo360AdvertiserLink|null
     */
    public function getDisplayVideo360AdvertiserLink()
    {
        return $this->display_video_360_advertiser_link;
    }

    public function hasDisplayVideo360AdvertiserLink()
    {
        return isset($this->display_video_360_advertiser_link);
    }

    public function clearDisplayVideo360AdvertiserLink()
    {
        unset($this->display_video_360_advertiser_link);
    }

    /**
     * Required. The DisplayVideo360AdvertiserLink to create.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.DisplayVideo360AdvertiserLink display_video_360_advertiser_link = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Analytics\Admin\V1alpha\DisplayVideo360AdvertiserLink $var
     * @return $this
     */
    public function setDisplayVideo360AdvertiserLink($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\DisplayVideo360AdvertiserLink::class);
        $this->display_video_360_advertiser_link = $var;

        return $this;
    }

}

