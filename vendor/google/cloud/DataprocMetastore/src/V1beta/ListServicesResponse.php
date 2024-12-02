<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/metastore/v1beta/metastore.proto

namespace Google\Cloud\Metastore\V1beta;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for
 * [DataprocMetastore.ListServices][google.cloud.metastore.v1beta.DataprocMetastore.ListServices].
 *
 * Generated from protobuf message <code>google.cloud.metastore.v1beta.ListServicesResponse</code>
 */
class ListServicesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The services in the specified location.
     *
     * Generated from protobuf field <code>repeated .google.cloud.metastore.v1beta.Service services = 1;</code>
     */
    private $services;
    /**
     * A token that can be sent as `page_token` to retrieve the next page. If this
     * field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';
    /**
     * Locations that could not be reached.
     *
     * Generated from protobuf field <code>repeated string unreachable = 3;</code>
     */
    private $unreachable;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Metastore\V1beta\Service[]|\Google\Protobuf\Internal\RepeatedField $services
     *           The services in the specified location.
     *     @type string $next_page_token
     *           A token that can be sent as `page_token` to retrieve the next page. If this
     *           field is omitted, there are no subsequent pages.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $unreachable
     *           Locations that could not be reached.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Metastore\V1Beta\Metastore::initOnce();
        parent::__construct($data);
    }

    /**
     * The services in the specified location.
     *
     * Generated from protobuf field <code>repeated .google.cloud.metastore.v1beta.Service services = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * The services in the specified location.
     *
     * Generated from protobuf field <code>repeated .google.cloud.metastore.v1beta.Service services = 1;</code>
     * @param \Google\Cloud\Metastore\V1beta\Service[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setServices($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Metastore\V1beta\Service::class);
        $this->services = $arr;

        return $this;
    }

    /**
     * A token that can be sent as `page_token` to retrieve the next page. If this
     * field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * A token that can be sent as `page_token` to retrieve the next page. If this
     * field is omitted, there are no subsequent pages.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

    /**
     * Locations that could not be reached.
     *
     * Generated from protobuf field <code>repeated string unreachable = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getUnreachable()
    {
        return $this->unreachable;
    }

    /**
     * Locations that could not be reached.
     *
     * Generated from protobuf field <code>repeated string unreachable = 3;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setUnreachable($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->unreachable = $arr;

        return $this;
    }

}

