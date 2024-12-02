<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/essentialcontacts/v1/service.proto

namespace Google\Cloud\EssentialContacts\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for the ComputeContacts method.
 *
 * Generated from protobuf message <code>google.cloud.essentialcontacts.v1.ComputeContactsRequest</code>
 */
class ComputeContactsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the resource to compute contacts for.
     * Format: organizations/{organization_id},
     * folders/{folder_id} or projects/{project_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * The categories of notifications to compute contacts for. If ALL is included
     * in this list, contacts subscribed to any notification category will be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.cloud.essentialcontacts.v1.NotificationCategory notification_categories = 6;</code>
     */
    private $notification_categories;
    /**
     * Optional. The maximum number of results to return from this request.
     * Non-positive values are ignored. The presence of `next_page_token` in the
     * response indicates that more results might be available.
     * If not specified, the default page_size is 100.
     *
     * Generated from protobuf field <code>int32 page_size = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $page_size = 0;
    /**
     * Optional. If present, retrieves the next batch of results from the
     * preceding call to this method. `page_token` must be the value of
     * `next_page_token` from the previous response. The values of other method
     * parameters should be identical to those in the previous call.
     *
     * Generated from protobuf field <code>string page_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The name of the resource to compute contacts for.
     *           Format: organizations/{organization_id},
     *           folders/{folder_id} or projects/{project_id}
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $notification_categories
     *           The categories of notifications to compute contacts for. If ALL is included
     *           in this list, contacts subscribed to any notification category will be
     *           returned.
     *     @type int $page_size
     *           Optional. The maximum number of results to return from this request.
     *           Non-positive values are ignored. The presence of `next_page_token` in the
     *           response indicates that more results might be available.
     *           If not specified, the default page_size is 100.
     *     @type string $page_token
     *           Optional. If present, retrieves the next batch of results from the
     *           preceding call to this method. `page_token` must be the value of
     *           `next_page_token` from the previous response. The values of other method
     *           parameters should be identical to those in the previous call.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Essentialcontacts\V1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the resource to compute contacts for.
     * Format: organizations/{organization_id},
     * folders/{folder_id} or projects/{project_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The name of the resource to compute contacts for.
     * Format: organizations/{organization_id},
     * folders/{folder_id} or projects/{project_id}
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
     * The categories of notifications to compute contacts for. If ALL is included
     * in this list, contacts subscribed to any notification category will be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.cloud.essentialcontacts.v1.NotificationCategory notification_categories = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getNotificationCategories()
    {
        return $this->notification_categories;
    }

    /**
     * The categories of notifications to compute contacts for. If ALL is included
     * in this list, contacts subscribed to any notification category will be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.cloud.essentialcontacts.v1.NotificationCategory notification_categories = 6;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setNotificationCategories($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::ENUM, \Google\Cloud\EssentialContacts\V1\NotificationCategory::class);
        $this->notification_categories = $arr;

        return $this;
    }

    /**
     * Optional. The maximum number of results to return from this request.
     * Non-positive values are ignored. The presence of `next_page_token` in the
     * response indicates that more results might be available.
     * If not specified, the default page_size is 100.
     *
     * Generated from protobuf field <code>int32 page_size = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Optional. The maximum number of results to return from this request.
     * Non-positive values are ignored. The presence of `next_page_token` in the
     * response indicates that more results might be available.
     * If not specified, the default page_size is 100.
     *
     * Generated from protobuf field <code>int32 page_size = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
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
     * Optional. If present, retrieves the next batch of results from the
     * preceding call to this method. `page_token` must be the value of
     * `next_page_token` from the previous response. The values of other method
     * parameters should be identical to those in the previous call.
     *
     * Generated from protobuf field <code>string page_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * Optional. If present, retrieves the next batch of results from the
     * preceding call to this method. `page_token` must be the value of
     * `next_page_token` from the previous response. The values of other method
     * parameters should be identical to those in the previous call.
     *
     * Generated from protobuf field <code>string page_token = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
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

