<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datastream/v1/datastream.proto

namespace Google\Cloud\Datastream\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for listing streams.
 *
 * Generated from protobuf message <code>google.cloud.datastream.v1.ListStreamsRequest</code>
 */
class ListStreamsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent that owns the collection of streams.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Maximum number of streams to return.
     * If unspecified, at most 50 streams will  be returned. The maximum
     * value is 1000; values above 1000 will be coerced to 1000.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     */
    private $page_size = 0;
    /**
     * Page token received from a previous `ListStreams` call.
     * Provide this to retrieve the subsequent page.
     * When paginating, all other parameters provided to `ListStreams`
     * must match the call that provided the page token.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     */
    private $page_token = '';
    /**
     * Filter request.
     *
     * Generated from protobuf field <code>string filter = 4;</code>
     */
    private $filter = '';
    /**
     * Order by fields for the result.
     *
     * Generated from protobuf field <code>string order_by = 5;</code>
     */
    private $order_by = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent that owns the collection of streams.
     *     @type int $page_size
     *           Maximum number of streams to return.
     *           If unspecified, at most 50 streams will  be returned. The maximum
     *           value is 1000; values above 1000 will be coerced to 1000.
     *     @type string $page_token
     *           Page token received from a previous `ListStreams` call.
     *           Provide this to retrieve the subsequent page.
     *           When paginating, all other parameters provided to `ListStreams`
     *           must match the call that provided the page token.
     *     @type string $filter
     *           Filter request.
     *     @type string $order_by
     *           Order by fields for the result.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datastream\V1\Datastream::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent that owns the collection of streams.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent that owns the collection of streams.
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
     * Maximum number of streams to return.
     * If unspecified, at most 50 streams will  be returned. The maximum
     * value is 1000; values above 1000 will be coerced to 1000.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Maximum number of streams to return.
     * If unspecified, at most 50 streams will  be returned. The maximum
     * value is 1000; values above 1000 will be coerced to 1000.
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
     * Page token received from a previous `ListStreams` call.
     * Provide this to retrieve the subsequent page.
     * When paginating, all other parameters provided to `ListStreams`
     * must match the call that provided the page token.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * Page token received from a previous `ListStreams` call.
     * Provide this to retrieve the subsequent page.
     * When paginating, all other parameters provided to `ListStreams`
     * must match the call that provided the page token.
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

    /**
     * Filter request.
     *
     * Generated from protobuf field <code>string filter = 4;</code>
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Filter request.
     *
     * Generated from protobuf field <code>string filter = 4;</code>
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
     * Order by fields for the result.
     *
     * Generated from protobuf field <code>string order_by = 5;</code>
     * @return string
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * Order by fields for the result.
     *
     * Generated from protobuf field <code>string order_by = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setOrderBy($var)
    {
        GPBUtil::checkString($var, True);
        $this->order_by = $var;

        return $this;
    }

}

