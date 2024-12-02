<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/contactcenterinsights/v1/contact_center_insights.proto

namespace Google\Cloud\ContactCenterInsights\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The response of listing issues.
 *
 * Generated from protobuf message <code>google.cloud.contactcenterinsights.v1.ListIssuesResponse</code>
 */
class ListIssuesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The issues that match the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.contactcenterinsights.v1.Issue issues = 1;</code>
     */
    private $issues;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\ContactCenterInsights\V1\Issue[]|\Google\Protobuf\Internal\RepeatedField $issues
     *           The issues that match the request.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Contactcenterinsights\V1\ContactCenterInsights::initOnce();
        parent::__construct($data);
    }

    /**
     * The issues that match the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.contactcenterinsights.v1.Issue issues = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * The issues that match the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.contactcenterinsights.v1.Issue issues = 1;</code>
     * @param \Google\Cloud\ContactCenterInsights\V1\Issue[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setIssues($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\ContactCenterInsights\V1\Issue::class);
        $this->issues = $arr;

        return $this;
    }

}

