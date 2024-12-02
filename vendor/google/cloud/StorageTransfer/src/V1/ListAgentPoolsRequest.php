<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/storagetransfer/v1/transfer.proto

namespace Google\Cloud\StorageTransfer\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request passed to ListAgentPools.
 *
 * Generated from protobuf message <code>google.storagetransfer.v1.ListAgentPoolsRequest</code>
 */
class ListAgentPoolsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The ID of the Google Cloud project that owns the job.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $project_id = '';
    /**
     * An optional list of query parameters specified as JSON text in the
     * form of:
     * `{"agentPoolNames":["agentpool1","agentpool2",...]}`
     * Since `agentPoolNames` support multiple values, its values must be
     * specified with array notation. When the filter is either empty or not
     * provided, the list returns all agent pools for the project.
     *
     * Generated from protobuf field <code>string filter = 2;</code>
     */
    private $filter = '';
    /**
     * The list page size. The max allowed value is `256`.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     */
    private $page_size = 0;
    /**
     * The list page token.
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
     *     @type string $project_id
     *           Required. The ID of the Google Cloud project that owns the job.
     *     @type string $filter
     *           An optional list of query parameters specified as JSON text in the
     *           form of:
     *           `{"agentPoolNames":["agentpool1","agentpool2",...]}`
     *           Since `agentPoolNames` support multiple values, its values must be
     *           specified with array notation. When the filter is either empty or not
     *           provided, the list returns all agent pools for the project.
     *     @type int $page_size
     *           The list page size. The max allowed value is `256`.
     *     @type string $page_token
     *           The list page token.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Storagetransfer\V1\Transfer::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The ID of the Google Cloud project that owns the job.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Required. The ID of the Google Cloud project that owns the job.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setProjectId($var)
    {
        GPBUtil::checkString($var, True);
        $this->project_id = $var;

        return $this;
    }

    /**
     * An optional list of query parameters specified as JSON text in the
     * form of:
     * `{"agentPoolNames":["agentpool1","agentpool2",...]}`
     * Since `agentPoolNames` support multiple values, its values must be
     * specified with array notation. When the filter is either empty or not
     * provided, the list returns all agent pools for the project.
     *
     * Generated from protobuf field <code>string filter = 2;</code>
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * An optional list of query parameters specified as JSON text in the
     * form of:
     * `{"agentPoolNames":["agentpool1","agentpool2",...]}`
     * Since `agentPoolNames` support multiple values, its values must be
     * specified with array notation. When the filter is either empty or not
     * provided, the list returns all agent pools for the project.
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
     * The list page size. The max allowed value is `256`.
     *
     * Generated from protobuf field <code>int32 page_size = 3;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * The list page size. The max allowed value is `256`.
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
     * The list page token.
     *
     * Generated from protobuf field <code>string page_token = 4;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * The list page token.
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

