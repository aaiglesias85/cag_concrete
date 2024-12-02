<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/data/v1alpha/analytics_data_api.proto

namespace Google\Analytics\Data\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The batch response containing multiple pivot reports.
 *
 * Generated from protobuf message <code>google.analytics.data.v1alpha.BatchRunPivotReportsResponse</code>
 */
class BatchRunPivotReportsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Individual responses. Each response has a separate pivot report request.
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.RunPivotReportResponse pivot_reports = 1;</code>
     */
    private $pivot_reports;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Analytics\Data\V1alpha\RunPivotReportResponse[]|\Google\Protobuf\Internal\RepeatedField $pivot_reports
     *           Individual responses. Each response has a separate pivot report request.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Data\V1Alpha\AnalyticsDataApi::initOnce();
        parent::__construct($data);
    }

    /**
     * Individual responses. Each response has a separate pivot report request.
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.RunPivotReportResponse pivot_reports = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPivotReports()
    {
        return $this->pivot_reports;
    }

    /**
     * Individual responses. Each response has a separate pivot report request.
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.RunPivotReportResponse pivot_reports = 1;</code>
     * @param \Google\Analytics\Data\V1alpha\RunPivotReportResponse[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPivotReports($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Analytics\Data\V1alpha\RunPivotReportResponse::class);
        $this->pivot_reports = $arr;

        return $this;
    }

}

