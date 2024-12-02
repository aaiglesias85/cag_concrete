<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/analytics_admin.proto

namespace Google\Analytics\Admin\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for ListCustomMetrics RPC.
 *
 * Generated from protobuf message <code>google.analytics.admin.v1alpha.ListCustomMetricsResponse</code>
 */
class ListCustomMetricsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * List of CustomMetrics.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.CustomMetric custom_metrics = 1;</code>
     */
    private $custom_metrics;
    /**
     * A token, which can be sent as `page_token` to retrieve the next page.
     * If this field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Analytics\Admin\V1alpha\CustomMetric[]|\Google\Protobuf\Internal\RepeatedField $custom_metrics
     *           List of CustomMetrics.
     *     @type string $next_page_token
     *           A token, which can be sent as `page_token` to retrieve the next page.
     *           If this field is omitted, there are no subsequent pages.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Admin\V1Alpha\AnalyticsAdmin::initOnce();
        parent::__construct($data);
    }

    /**
     * List of CustomMetrics.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.CustomMetric custom_metrics = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getCustomMetrics()
    {
        return $this->custom_metrics;
    }

    /**
     * List of CustomMetrics.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.CustomMetric custom_metrics = 1;</code>
     * @param \Google\Analytics\Admin\V1alpha\CustomMetric[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setCustomMetrics($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Analytics\Admin\V1alpha\CustomMetric::class);
        $this->custom_metrics = $arr;

        return $this;
    }

    /**
     * A token, which can be sent as `page_token` to retrieve the next page.
     * If this field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * A token, which can be sent as `page_token` to retrieve the next page.
     * If this field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}

