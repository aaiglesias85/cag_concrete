<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/networksecurity/v1beta1/client_tls_policy.proto

namespace Google\Cloud\NetworkSecurity\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request used by the ListClientTlsPolicies method.
 *
 * Generated from protobuf message <code>google.cloud.networksecurity.v1beta1.ListClientTlsPoliciesRequest</code>
 */
class ListClientTlsPoliciesRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The project and location from which the ClientTlsPolicies should
     * be listed, specified in the format `projects/&#42;&#47;locations/{location}`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Maximum number of ClientTlsPolicies to return per call.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     */
    private $page_size = 0;
    /**
     * The value returned by the last `ListClientTlsPoliciesResponse`
     * Indicates that this is a continuation of a prior
     * `ListClientTlsPolicies` call, and that the system
     * should return the next page of data.
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
     *     @type string $parent
     *           Required. The project and location from which the ClientTlsPolicies should
     *           be listed, specified in the format `projects/&#42;&#47;locations/{location}`.
     *     @type int $page_size
     *           Maximum number of ClientTlsPolicies to return per call.
     *     @type string $page_token
     *           The value returned by the last `ListClientTlsPoliciesResponse`
     *           Indicates that this is a continuation of a prior
     *           `ListClientTlsPolicies` call, and that the system
     *           should return the next page of data.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Networksecurity\V1Beta1\ClientTlsPolicy::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The project and location from which the ClientTlsPolicies should
     * be listed, specified in the format `projects/&#42;&#47;locations/{location}`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The project and location from which the ClientTlsPolicies should
     * be listed, specified in the format `projects/&#42;&#47;locations/{location}`.
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
     * Maximum number of ClientTlsPolicies to return per call.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Maximum number of ClientTlsPolicies to return per call.
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
     * The value returned by the last `ListClientTlsPoliciesResponse`
     * Indicates that this is a continuation of a prior
     * `ListClientTlsPolicies` call, and that the system
     * should return the next page of data.
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * The value returned by the last `ListClientTlsPoliciesResponse`
     * Indicates that this is a continuation of a prior
     * `ListClientTlsPolicies` call, and that the system
     * should return the next page of data.
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

