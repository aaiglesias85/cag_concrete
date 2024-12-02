<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/osconfig/v1/os_policy_assignments.proto

namespace Google\Cloud\OsConfig\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A request message to list revisions for a OS policy assignment
 *
 * Generated from protobuf message <code>google.cloud.osconfig.v1.ListOSPolicyAssignmentRevisionsRequest</code>
 */
class ListOSPolicyAssignmentRevisionsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the OS policy assignment to list revisions for.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $name = '';
    /**
     * The maximum number of revisions to return.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     */
    private $page_size = 0;
    /**
     * A pagination token returned from a previous call to
     * `ListOSPolicyAssignmentRevisions` that indicates where this listing should
     * continue from.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     */
    private $page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Required. The name of the OS policy assignment to list revisions for.
     *     @type int $page_size
     *           The maximum number of revisions to return.
     *     @type string $page_token
     *           A pagination token returned from a previous call to
     *           `ListOSPolicyAssignmentRevisions` that indicates where this listing should
     *           continue from.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Osconfig\V1\OsPolicyAssignments::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the OS policy assignment to list revisions for.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Required. The name of the OS policy assignment to list revisions for.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * The maximum number of revisions to return.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * The maximum number of revisions to return.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setPageSize($var)
    {
        GPBUtil::checkInt32($var);
        $this->page_size = $var;

        return $this;
    }

    /**
     * A pagination token returned from a previous call to
     * `ListOSPolicyAssignmentRevisions` that indicates where this listing should
     * continue from.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * A pagination token returned from a previous call to
     * `ListOSPolicyAssignmentRevisions` that indicates where this listing should
     * continue from.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->page_token = $var;

        return $this;
    }

}

