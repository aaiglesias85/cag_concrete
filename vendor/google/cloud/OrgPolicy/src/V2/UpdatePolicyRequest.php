<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/orgpolicy/v2/orgpolicy.proto

namespace Google\Cloud\OrgPolicy\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request sent to the [UpdatePolicyRequest]
 * [google.cloud.orgpolicy.v2.OrgPolicy.UpdatePolicy] method.
 *
 * Generated from protobuf message <code>google.cloud.orgpolicy.v2.UpdatePolicyRequest</code>
 */
class UpdatePolicyRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. `Policy` to update.
     *
     * Generated from protobuf field <code>.google.cloud.orgpolicy.v2.Policy policy = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $policy = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\OrgPolicy\V2\Policy $policy
     *           Required. `Policy` to update.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Orgpolicy\V2\Orgpolicy::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. `Policy` to update.
     *
     * Generated from protobuf field <code>.google.cloud.orgpolicy.v2.Policy policy = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\OrgPolicy\V2\Policy|null
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    public function hasPolicy()
    {
        return isset($this->policy);
    }

    public function clearPolicy()
    {
        unset($this->policy);
    }

    /**
     * Required. `Policy` to update.
     *
     * Generated from protobuf field <code>.google.cloud.orgpolicy.v2.Policy policy = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\OrgPolicy\V2\Policy $var
     * @return $this
     */
    public function setPolicy($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\OrgPolicy\V2\Policy::class);
        $this->policy = $var;

        return $this;
    }

}

