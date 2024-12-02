<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/compute/v1/compute.proto

namespace Google\Cloud\Compute\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 *
 * Generated from protobuf message <code>google.cloud.compute.v1.TestFailure</code>
 */
class TestFailure extends \Google\Protobuf\Internal\Message
{
    /**
     * The actual output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string actual_output_url = 287075458;</code>
     */
    private $actual_output_url = null;
    /**
     * Actual HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 actual_redirect_response_code = 42926553;</code>
     */
    private $actual_redirect_response_code = null;
    /**
     * BackendService or BackendBucket returned by load balancer.
     *
     * Generated from protobuf field <code>optional string actual_service = 440379652;</code>
     */
    private $actual_service = null;
    /**
     * The expected output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string expected_output_url = 433967384;</code>
     */
    private $expected_output_url = null;
    /**
     * Expected HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 expected_redirect_response_code = 18888047;</code>
     */
    private $expected_redirect_response_code = null;
    /**
     * Expected BackendService or BackendBucket resource the given URL should be mapped to.
     *
     * Generated from protobuf field <code>optional string expected_service = 133987374;</code>
     */
    private $expected_service = null;
    /**
     * HTTP headers of the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.UrlMapTestHeader headers = 258436998;</code>
     */
    private $headers;
    /**
     * Host portion of the URL.
     *
     * Generated from protobuf field <code>optional string host = 3208616;</code>
     */
    private $host = null;
    /**
     * Path portion including query parameters in the URL.
     *
     * Generated from protobuf field <code>optional string path = 3433509;</code>
     */
    private $path = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $actual_output_url
     *           The actual output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *     @type int $actual_redirect_response_code
     *           Actual HTTP status code for rule with `urlRedirect` calculated by load balancer
     *     @type string $actual_service
     *           BackendService or BackendBucket returned by load balancer.
     *     @type string $expected_output_url
     *           The expected output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *     @type int $expected_redirect_response_code
     *           Expected HTTP status code for rule with `urlRedirect` calculated by load balancer
     *     @type string $expected_service
     *           Expected BackendService or BackendBucket resource the given URL should be mapped to.
     *     @type \Google\Cloud\Compute\V1\UrlMapTestHeader[]|\Google\Protobuf\Internal\RepeatedField $headers
     *           HTTP headers of the request.
     *     @type string $host
     *           Host portion of the URL.
     *     @type string $path
     *           Path portion including query parameters in the URL.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Compute\V1\Compute::initOnce();
        parent::__construct($data);
    }

    /**
     * The actual output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string actual_output_url = 287075458;</code>
     * @return string
     */
    public function getActualOutputUrl()
    {
        return isset($this->actual_output_url) ? $this->actual_output_url : '';
    }

    public function hasActualOutputUrl()
    {
        return isset($this->actual_output_url);
    }

    public function clearActualOutputUrl()
    {
        unset($this->actual_output_url);
    }

    /**
     * The actual output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string actual_output_url = 287075458;</code>
     * @param string $var
     * @return $this
     */
    public function setActualOutputUrl($var)
    {
        GPBUtil::checkString($var, True);
        $this->actual_output_url = $var;

        return $this;
    }

    /**
     * Actual HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 actual_redirect_response_code = 42926553;</code>
     * @return int
     */
    public function getActualRedirectResponseCode()
    {
        return isset($this->actual_redirect_response_code) ? $this->actual_redirect_response_code : 0;
    }

    public function hasActualRedirectResponseCode()
    {
        return isset($this->actual_redirect_response_code);
    }

    public function clearActualRedirectResponseCode()
    {
        unset($this->actual_redirect_response_code);
    }

    /**
     * Actual HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 actual_redirect_response_code = 42926553;</code>
     * @param int $var
     * @return $this
     */
    public function setActualRedirectResponseCode($var)
    {
        GPBUtil::checkInt32($var);
        $this->actual_redirect_response_code = $var;

        return $this;
    }

    /**
     * BackendService or BackendBucket returned by load balancer.
     *
     * Generated from protobuf field <code>optional string actual_service = 440379652;</code>
     * @return string
     */
    public function getActualService()
    {
        return isset($this->actual_service) ? $this->actual_service : '';
    }

    public function hasActualService()
    {
        return isset($this->actual_service);
    }

    public function clearActualService()
    {
        unset($this->actual_service);
    }

    /**
     * BackendService or BackendBucket returned by load balancer.
     *
     * Generated from protobuf field <code>optional string actual_service = 440379652;</code>
     * @param string $var
     * @return $this
     */
    public function setActualService($var)
    {
        GPBUtil::checkString($var, True);
        $this->actual_service = $var;

        return $this;
    }

    /**
     * The expected output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string expected_output_url = 433967384;</code>
     * @return string
     */
    public function getExpectedOutputUrl()
    {
        return isset($this->expected_output_url) ? $this->expected_output_url : '';
    }

    public function hasExpectedOutputUrl()
    {
        return isset($this->expected_output_url);
    }

    public function clearExpectedOutputUrl()
    {
        unset($this->expected_output_url);
    }

    /**
     * The expected output URL evaluated by a load balancer containing the scheme, host, path and query parameters.
     *
     * Generated from protobuf field <code>optional string expected_output_url = 433967384;</code>
     * @param string $var
     * @return $this
     */
    public function setExpectedOutputUrl($var)
    {
        GPBUtil::checkString($var, True);
        $this->expected_output_url = $var;

        return $this;
    }

    /**
     * Expected HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 expected_redirect_response_code = 18888047;</code>
     * @return int
     */
    public function getExpectedRedirectResponseCode()
    {
        return isset($this->expected_redirect_response_code) ? $this->expected_redirect_response_code : 0;
    }

    public function hasExpectedRedirectResponseCode()
    {
        return isset($this->expected_redirect_response_code);
    }

    public function clearExpectedRedirectResponseCode()
    {
        unset($this->expected_redirect_response_code);
    }

    /**
     * Expected HTTP status code for rule with `urlRedirect` calculated by load balancer
     *
     * Generated from protobuf field <code>optional int32 expected_redirect_response_code = 18888047;</code>
     * @param int $var
     * @return $this
     */
    public function setExpectedRedirectResponseCode($var)
    {
        GPBUtil::checkInt32($var);
        $this->expected_redirect_response_code = $var;

        return $this;
    }

    /**
     * Expected BackendService or BackendBucket resource the given URL should be mapped to.
     *
     * Generated from protobuf field <code>optional string expected_service = 133987374;</code>
     * @return string
     */
    public function getExpectedService()
    {
        return isset($this->expected_service) ? $this->expected_service : '';
    }

    public function hasExpectedService()
    {
        return isset($this->expected_service);
    }

    public function clearExpectedService()
    {
        unset($this->expected_service);
    }

    /**
     * Expected BackendService or BackendBucket resource the given URL should be mapped to.
     *
     * Generated from protobuf field <code>optional string expected_service = 133987374;</code>
     * @param string $var
     * @return $this
     */
    public function setExpectedService($var)
    {
        GPBUtil::checkString($var, True);
        $this->expected_service = $var;

        return $this;
    }

    /**
     * HTTP headers of the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.UrlMapTestHeader headers = 258436998;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * HTTP headers of the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.compute.v1.UrlMapTestHeader headers = 258436998;</code>
     * @param \Google\Cloud\Compute\V1\UrlMapTestHeader[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setHeaders($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Compute\V1\UrlMapTestHeader::class);
        $this->headers = $arr;

        return $this;
    }

    /**
     * Host portion of the URL.
     *
     * Generated from protobuf field <code>optional string host = 3208616;</code>
     * @return string
     */
    public function getHost()
    {
        return isset($this->host) ? $this->host : '';
    }

    public function hasHost()
    {
        return isset($this->host);
    }

    public function clearHost()
    {
        unset($this->host);
    }

    /**
     * Host portion of the URL.
     *
     * Generated from protobuf field <code>optional string host = 3208616;</code>
     * @param string $var
     * @return $this
     */
    public function setHost($var)
    {
        GPBUtil::checkString($var, True);
        $this->host = $var;

        return $this;
    }

    /**
     * Path portion including query parameters in the URL.
     *
     * Generated from protobuf field <code>optional string path = 3433509;</code>
     * @return string
     */
    public function getPath()
    {
        return isset($this->path) ? $this->path : '';
    }

    public function hasPath()
    {
        return isset($this->path);
    }

    public function clearPath()
    {
        unset($this->path);
    }

    /**
     * Path portion including query parameters in the URL.
     *
     * Generated from protobuf field <code>optional string path = 3433509;</code>
     * @param string $var
     * @return $this
     */
    public function setPath($var)
    {
        GPBUtil::checkString($var, True);
        $this->path = $var;

        return $this;
    }

}

