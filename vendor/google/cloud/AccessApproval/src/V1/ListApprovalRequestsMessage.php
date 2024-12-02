<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/accessapproval/v1/accessapproval.proto

namespace Google\Cloud\AccessApproval\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request to list approval requests.
 *
 * Generated from protobuf message <code>google.cloud.accessapproval.v1.ListApprovalRequestsMessage</code>
 */
class ListApprovalRequestsMessage extends \Google\Protobuf\Internal\Message
{
    /**
     * The parent resource. This may be "projects/{project}",
     * "folders/{folder}", or "organizations/{organization}".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * A filter on the type of approval requests to retrieve. Must be one of the
     * following values:
     *   * [not set]: Requests that are pending or have active approvals.
     *   * ALL: All requests.
     *   * PENDING: Only pending requests.
     *   * ACTIVE: Only active (i.e. currently approved) requests.
     *   * DISMISSED: Only requests that have been dismissed, or requests that
     *     are not approved and past expiration.
     *   * EXPIRED: Only requests that have been approved, and the approval has
     *     expired.
     *   * HISTORY: Active, dismissed and expired requests.
     *
     * Generated from protobuf field <code>string filter = 2;</code>
     */
    private $filter = '';
    /**
     * Requested page size.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     */
    private $page_size = 0;
    /**
     * A token identifying the page of results to return.
     *
     * Generated from protobuf field <code>string page_token = 4;</code>
     */
    private $page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           The parent resource. This may be "projects/{project}",
     *           "folders/{folder}", or "organizations/{organization}".
     *     @type string $filter
     *           A filter on the type of approval requests to retrieve. Must be one of the
     *           following values:
     *             * [not set]: Requests that are pending or have active approvals.
     *             * ALL: All requests.
     *             * PENDING: Only pending requests.
     *             * ACTIVE: Only active (i.e. currently approved) requests.
     *             * DISMISSED: Only requests that have been dismissed, or requests that
     *               are not approved and past expiration.
     *             * EXPIRED: Only requests that have been approved, and the approval has
     *               expired.
     *             * HISTORY: Active, dismissed and expired requests.
     *     @type int $page_size
     *           Requested page size.
     *     @type string $page_token
     *           A token identifying the page of results to return.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Accessapproval\V1\Accessapproval::initOnce();
        parent::__construct($data);
    }

    /**
     * The parent resource. This may be "projects/{project}",
     * "folders/{folder}", or "organizations/{organization}".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * The parent resource. This may be "projects/{project}",
     * "folders/{folder}", or "organizations/{organization}".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
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
     * A filter on the type of approval requests to retrieve. Must be one of the
     * following values:
     *   * [not set]: Requests that are pending or have active approvals.
     *   * ALL: All requests.
     *   * PENDING: Only pending requests.
     *   * ACTIVE: Only active (i.e. currently approved) requests.
     *   * DISMISSED: Only requests that have been dismissed, or requests that
     *     are not approved and past expiration.
     *   * EXPIRED: Only requests that have been approved, and the approval has
     *     expired.
     *   * HISTORY: Active, dismissed and expired requests.
     *
     * Generated from protobuf field <code>string filter = 2;</code>
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * A filter on the type of approval requests to retrieve. Must be one of the
     * following values:
     *   * [not set]: Requests that are pending or have active approvals.
     *   * ALL: All requests.
     *   * PENDING: Only pending requests.
     *   * ACTIVE: Only active (i.e. currently approved) requests.
     *   * DISMISSED: Only requests that have been dismissed, or requests that
     *     are not approved and past expiration.
     *   * EXPIRED: Only requests that have been approved, and the approval has
     *     expired.
     *   * HISTORY: Active, dismissed and expired requests.
     *
     * Generated from protobuf field <code>string filter = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setFilter($var)
    {
        GPBUtil::checkString($var, True);
        $this->filter = $var;

        return $this;
    }

    /**
     * Requested page size.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Requested page size.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
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
     * A token identifying the page of results to return.
     *
     * Generated from protobuf field <code>string page_token = 4;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * A token identifying the page of results to return.
     *
     * Generated from protobuf field <code>string page_token = 4;</code>
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

