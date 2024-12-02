<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/contactcenterinsights/v1/resources.proto

namespace Google\Cloud\ContactCenterInsights\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Aggregated statistics about an issue model.
 *
 * Generated from protobuf message <code>google.cloud.contactcenterinsights.v1.IssueModelLabelStats</code>
 */
class IssueModelLabelStats extends \Google\Protobuf\Internal\Message
{
    /**
     * Number of conversations the issue model has analyzed at this point in time.
     *
     * Generated from protobuf field <code>int64 analyzed_conversations_count = 1;</code>
     */
    private $analyzed_conversations_count = 0;
    /**
     * Number of analyzed conversations for which no issue was applicable at this
     * point in time.
     *
     * Generated from protobuf field <code>int64 unclassified_conversations_count = 2;</code>
     */
    private $unclassified_conversations_count = 0;
    /**
     * Statistics on each issue. Key is the issue's resource name.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.contactcenterinsights.v1.IssueModelLabelStats.IssueStats> issue_stats = 3;</code>
     */
    private $issue_stats;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $analyzed_conversations_count
     *           Number of conversations the issue model has analyzed at this point in time.
     *     @type int|string $unclassified_conversations_count
     *           Number of analyzed conversations for which no issue was applicable at this
     *           point in time.
     *     @type array|\Google\Protobuf\Internal\MapField $issue_stats
     *           Statistics on each issue. Key is the issue's resource name.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Contactcenterinsights\V1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * Number of conversations the issue model has analyzed at this point in time.
     *
     * Generated from protobuf field <code>int64 analyzed_conversations_count = 1;</code>
     * @return int|string
     */
    public function getAnalyzedConversationsCount()
    {
        return $this->analyzed_conversations_count;
    }

    /**
     * Number of conversations the issue model has analyzed at this point in time.
     *
     * Generated from protobuf field <code>int64 analyzed_conversations_count = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setAnalyzedConversationsCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->analyzed_conversations_count = $var;

        return $this;
    }

    /**
     * Number of analyzed conversations for which no issue was applicable at this
     * point in time.
     *
     * Generated from protobuf field <code>int64 unclassified_conversations_count = 2;</code>
     * @return int|string
     */
    public function getUnclassifiedConversationsCount()
    {
        return $this->unclassified_conversations_count;
    }

    /**
     * Number of analyzed conversations for which no issue was applicable at this
     * point in time.
     *
     * Generated from protobuf field <code>int64 unclassified_conversations_count = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setUnclassifiedConversationsCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->unclassified_conversations_count = $var;

        return $this;
    }

    /**
     * Statistics on each issue. Key is the issue's resource name.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.contactcenterinsights.v1.IssueModelLabelStats.IssueStats> issue_stats = 3;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getIssueStats()
    {
        return $this->issue_stats;
    }

    /**
     * Statistics on each issue. Key is the issue's resource name.
     *
     * Generated from protobuf field <code>map<string, .google.cloud.contactcenterinsights.v1.IssueModelLabelStats.IssueStats> issue_stats = 3;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setIssueStats($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\ContactCenterInsights\V1\IssueModelLabelStats\IssueStats::class);
        $this->issue_stats = $arr;

        return $this;
    }

}

