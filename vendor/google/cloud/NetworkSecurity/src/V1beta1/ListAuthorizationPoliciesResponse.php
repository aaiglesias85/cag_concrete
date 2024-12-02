<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/networksecurity/v1beta1/authorization_policy.proto

namespace Google\Cloud\NetworkSecurity\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response returned by the ListAuthorizationPolicies method.
 *
 * Generated from protobuf message <code>google.cloud.networksecurity.v1beta1.ListAuthorizationPoliciesResponse</code>
 */
class ListAuthorizationPoliciesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * List of AuthorizationPolicies resources.
     *
     * Generated from protobuf field <code>repeated .google.cloud.networksecurity.v1beta1.AuthorizationPolicy authorization_policies = 1;</code>
     */
    private $authorization_policies;
    /**
     * If there might be more results than those appearing in this response, then
     * `next_page_token` is included. To get the next set of results, call this
     * method again using the value of `next_page_token` as `page_token`.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\NetworkSecurity\V1beta1\AuthorizationPolicy[]|\Google\Protobuf\Internal\RepeatedField $authorization_policies
     *           List of AuthorizationPolicies resources.
     *     @type string $next_page_token
     *           If there might be more results than those appearing in this response, then
     *           `next_page_token` is included. To get the next set of results, call this
     *           method again using the value of `next_page_token` as `page_token`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Networksecurity\V1Beta1\AuthorizationPolicy::initOnce();
        parent::__construct($data);
    }

    /**
     * List of AuthorizationPolicies resources.
     *
     * Generated from protobuf field <code>repeated .google.cloud.networksecurity.v1beta1.AuthorizationPolicy authorization_policies = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAuthorizationPolicies()
    {
        return $this->authorization_policies;
    }

    /**
     * List of AuthorizationPolicies resources.
     *
     * Generated from protobuf field <code>repeated .google.cloud.networksecurity.v1beta1.AuthorizationPolicy authorization_policies = 1;</code>
     * @param \Google\Cloud\NetworkSecurity\V1beta1\AuthorizationPolicy[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAuthorizationPolicies($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\NetworkSecurity\V1beta1\AuthorizationPolicy::class);
        $this->authorization_policies = $arr;

        return $this;
    }

    /**
     * If there might be more results than those appearing in this response, then
     * `next_page_token` is included. To get the next set of results, call this
     * method again using the value of `next_page_token` as `page_token`.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * If there might be more results than those appearing in this response, then
     * `next_page_token` is included. To get the next set of results, call this
     * method again using the value of `next_page_token` as `page_token`.
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

}

