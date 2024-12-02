<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/logging/v2/logging_config.proto

namespace Google\Cloud\Logging\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The parameters to `CreateExclusion`.
 *
 * Generated from protobuf message <code>google.logging.v2.CreateExclusionRequest</code>
 */
class CreateExclusionRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent resource in which to create the exclusion:
     *     "projects/[PROJECT_ID]"
     *     "organizations/[ORGANIZATION_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]"
     *     "folders/[FOLDER_ID]"
     * For examples:
     *   `"projects/my-logging-project"`
     *   `"organizations/123456789"`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The new exclusion, whose `name` parameter is an exclusion name
     * that is not already used in the parent resource.
     *
     * Generated from protobuf field <code>.google.logging.v2.LogExclusion exclusion = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $exclusion = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent resource in which to create the exclusion:
     *               "projects/[PROJECT_ID]"
     *               "organizations/[ORGANIZATION_ID]"
     *               "billingAccounts/[BILLING_ACCOUNT_ID]"
     *               "folders/[FOLDER_ID]"
     *           For examples:
     *             `"projects/my-logging-project"`
     *             `"organizations/123456789"`
     *     @type \Google\Cloud\Logging\V2\LogExclusion $exclusion
     *           Required. The new exclusion, whose `name` parameter is an exclusion name
     *           that is not already used in the parent resource.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Logging\V2\LoggingConfig::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent resource in which to create the exclusion:
     *     "projects/[PROJECT_ID]"
     *     "organizations/[ORGANIZATION_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]"
     *     "folders/[FOLDER_ID]"
     * For examples:
     *   `"projects/my-logging-project"`
     *   `"organizations/123456789"`
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent resource in which to create the exclusion:
     *     "projects/[PROJECT_ID]"
     *     "organizations/[ORGANIZATION_ID]"
     *     "billingAccounts/[BILLING_ACCOUNT_ID]"
     *     "folders/[FOLDER_ID]"
     * For examples:
     *   `"projects/my-logging-project"`
     *   `"organizations/123456789"`
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
     * Required. The new exclusion, whose `name` parameter is an exclusion name
     * that is not already used in the parent resource.
     *
     * Generated from protobuf field <code>.google.logging.v2.LogExclusion exclusion = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Logging\V2\LogExclusion|null
     */
    public function getExclusion()
    {
        return $this->exclusion;
    }

    public function hasExclusion()
    {
        return isset($this->exclusion);
    }

    public function clearExclusion()
    {
        unset($this->exclusion);
    }

    /**
     * Required. The new exclusion, whose `name` parameter is an exclusion name
     * that is not already used in the parent resource.
     *
     * Generated from protobuf field <code>.google.logging.v2.LogExclusion exclusion = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Logging\V2\LogExclusion $var
     * @return $this
     */
    public function setExclusion($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Logging\V2\LogExclusion::class);
        $this->exclusion = $var;

        return $this;
    }

}

