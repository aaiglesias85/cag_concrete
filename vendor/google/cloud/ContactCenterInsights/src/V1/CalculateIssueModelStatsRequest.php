<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/contactcenterinsights/v1/contact_center_insights.proto

namespace Google\Cloud\ContactCenterInsights\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request to get statistics of an issue model.
 *
 * Generated from protobuf message <code>google.cloud.contactcenterinsights.v1.CalculateIssueModelStatsRequest</code>
 */
class CalculateIssueModelStatsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The resource name of the issue model to query against.
     *
     * Generated from protobuf field <code>string issue_model = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $issue_model = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $issue_model
     *           Required. The resource name of the issue model to query against.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Contactcenterinsights\V1\ContactCenterInsights::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The resource name of the issue model to query against.
     *
     * Generated from protobuf field <code>string issue_model = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getIssueModel()
    {
        return $this->issue_model;
    }

    /**
     * Required. The resource name of the issue model to query against.
     *
     * Generated from protobuf field <code>string issue_model = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setIssueModel($var)
    {
        GPBUtil::checkString($var, True);
        $this->issue_model = $var;

        return $this;
    }

}

