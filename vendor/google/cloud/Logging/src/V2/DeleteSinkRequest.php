<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/logging/v2/logging_config.proto

namespace Google\Cloud\Logging\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The parameters to `DeleteSink`.
 *
 * Generated from protobuf message <code>google.logging.v2.DeleteSinkRequest</code>
 */
class DeleteSinkRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The full resource name of the sink to delete, including the parent
     * resource and the sink identifier:
     *     "projects/[PROJECT_ID]/sinks/[SINK_ID]"
     *     "organizations/[ORGANIZATION_ID]/sinks/[SINK_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]/sinks/[SINK_ID]"
     *     "folders/[FOLDER_ID]/sinks/[SINK_ID]"
     * For example:
     *   `"projects/my-project/sinks/my-sink"`
     *
     * Generated from protobuf field <code>string sink_name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $sink_name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $sink_name
     *           Required. The full resource name of the sink to delete, including the parent
     *           resource and the sink identifier:
     *               "projects/[PROJECT_ID]/sinks/[SINK_ID]"
     *               "organizations/[ORGANIZATION_ID]/sinks/[SINK_ID]"
     *               "billingAccounts/[BILLING_ACCOUNT_ID]/sinks/[SINK_ID]"
     *               "folders/[FOLDER_ID]/sinks/[SINK_ID]"
     *           For example:
     *             `"projects/my-project/sinks/my-sink"`
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Logging\V2\LoggingConfig::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The full resource name of the sink to delete, including the parent
     * resource and the sink identifier:
     *     "projects/[PROJECT_ID]/sinks/[SINK_ID]"
     *     "organizations/[ORGANIZATION_ID]/sinks/[SINK_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]/sinks/[SINK_ID]"
     *     "folders/[FOLDER_ID]/sinks/[SINK_ID]"
     * For example:
     *   `"projects/my-project/sinks/my-sink"`
     *
     * Generated from protobuf field <code>string sink_name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getSinkName()
    {
        return $this->sink_name;
    }

    /**
     * Required. The full resource name of the sink to delete, including the parent
     * resource and the sink identifier:
     *     "projects/[PROJECT_ID]/sinks/[SINK_ID]"
     *     "organizations/[ORGANIZATION_ID]/sinks/[SINK_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]/sinks/[SINK_ID]"
     *     "folders/[FOLDER_ID]/sinks/[SINK_ID]"
     * For example:
     *   `"projects/my-project/sinks/my-sink"`
     *
     * Generated from protobuf field <code>string sink_name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setSinkName($var)
    {
        GPBUtil::checkString($var, True);
        $this->sink_name = $var;

        return $this;
    }

}

