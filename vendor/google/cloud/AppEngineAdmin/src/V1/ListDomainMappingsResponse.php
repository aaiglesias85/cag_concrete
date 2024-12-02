<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/appengine/v1/appengine.proto

namespace Google\Cloud\AppEngine\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for `DomainMappings.ListDomainMappings`.
 *
 * Generated from protobuf message <code>google.appengine.v1.ListDomainMappingsResponse</code>
 */
class ListDomainMappingsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The domain mappings for the application.
     *
     * Generated from protobuf field <code>repeated .google.appengine.v1.DomainMapping domain_mappings = 1;</code>
     */
    private $domain_mappings;
    /**
     * Continuation token for fetching the next page of results.
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
     *     @type \Google\Cloud\AppEngine\V1\DomainMapping[]|\Google\Protobuf\Internal\RepeatedField $domain_mappings
     *           The domain mappings for the application.
     *     @type string $next_page_token
     *           Continuation token for fetching the next page of results.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Appengine\V1\Appengine::initOnce();
        parent::__construct($data);
    }

    /**
     * The domain mappings for the application.
     *
     * Generated from protobuf field <code>repeated .google.appengine.v1.DomainMapping domain_mappings = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDomainMappings()
    {
        return $this->domain_mappings;
    }

    /**
     * The domain mappings for the application.
     *
     * Generated from protobuf field <code>repeated .google.appengine.v1.DomainMapping domain_mappings = 1;</code>
     * @param \Google\Cloud\AppEngine\V1\DomainMapping[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDomainMappings($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\AppEngine\V1\DomainMapping::class);
        $this->domain_mappings = $arr;

        return $this;
    }

    /**
     * Continuation token for fetching the next page of results.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * Continuation token for fetching the next page of results.
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

