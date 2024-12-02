<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/monitoring/v3/uptime_service.proto

namespace Google\Cloud\Monitoring\V3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The protocol for the `ListUptimeCheckConfigs` request.
 *
 * Generated from protobuf message <code>google.monitoring.v3.ListUptimeCheckConfigsRequest</code>
 */
class ListUptimeCheckConfigsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The
     * [project](https://cloud.google.com/monitoring/api/v3#project_name) whose
     * Uptime check configurations are listed. The format is:
     *     projects/[PROJECT_ID_OR_NUMBER]
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * The maximum number of results to return in a single response. The server
     * may further constrain the maximum number of results returned in a single
     * page. If the page_size is <=0, the server will decide the number of results
     * to be returned.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     */
    private $page_size = 0;
    /**
     * If this field is not empty then it must contain the `nextPageToken` value
     * returned by a previous call to this method.  Using this field causes the
     * method to return more results from the previous method call.
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
     *           Required. The
     *           [project](https://cloud.google.com/monitoring/api/v3#project_name) whose
     *           Uptime check configurations are listed. The format is:
     *               projects/[PROJECT_ID_OR_NUMBER]
     *     @type int $page_size
     *           The maximum number of results to return in a single response. The server
     *           may further constrain the maximum number of results returned in a single
     *           page. If the page_size is <=0, the server will decide the number of results
     *           to be returned.
     *     @type string $page_token
     *           If this field is not empty then it must contain the `nextPageToken` value
     *           returned by a previous call to this method.  Using this field causes the
     *           method to return more results from the previous method call.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Monitoring\V3\UptimeService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The
     * [project](https://cloud.google.com/monitoring/api/v3#project_name) whose
     * Uptime check configurations are listed. The format is:
     *     projects/[PROJECT_ID_OR_NUMBER]
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The
     * [project](https://cloud.google.com/monitoring/api/v3#project_name) whose
     * Uptime check configurations are listed. The format is:
     *     projects/[PROJECT_ID_OR_NUMBER]
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
     * The maximum number of results to return in a single response. The server
     * may further constrain the maximum number of results returned in a single
     * page. If the page_size is <=0, the server will decide the number of results
     * to be returned.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * The maximum number of results to return in a single response. The server
     * may further constrain the maximum number of results returned in a single
     * page. If the page_size is <=0, the server will decide the number of results
     * to be returned.
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
     * If this field is not empty then it must contain the `nextPageToken` value
     * returned by a previous call to this method.  Using this field causes the
     * method to return more results from the previous method call.
     *
     * Generated from protobuf field <code>string page_token = 4;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * If this field is not empty then it must contain the `nextPageToken` value
     * returned by a previous call to this method.  Using this field causes the
     * method to return more results from the previous method call.
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

