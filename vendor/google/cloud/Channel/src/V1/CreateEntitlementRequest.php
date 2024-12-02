<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/channel/v1/service.proto

namespace Google\Cloud\Channel\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for [CloudChannelService.CreateEntitlement][google.cloud.channel.v1.CloudChannelService.CreateEntitlement]
 *
 * Generated from protobuf message <code>google.cloud.channel.v1.CreateEntitlementRequest</code>
 */
class CreateEntitlementRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The resource name of the reseller's customer account in which to create the
     * entitlement.
     * Parent uses the format: accounts/{account_id}/customers/{customer_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The entitlement to create.
     *
     * Generated from protobuf field <code>.google.cloud.channel.v1.Entitlement entitlement = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $entitlement = null;
    /**
     * Optional. You can specify an optional unique request ID, and if you need to retry
     * your request, the server will know to ignore the request if it's complete.
     * For example, you make an initial request and the request times out. If you
     * make the request again with the same request ID, the server can check if
     * it received the original operation with the same request ID. If it did, it
     * will ignore the second request.
     * The request ID must be a valid [UUID](https://tools.ietf.org/html/rfc4122)
     * with the exception that zero UUID is not supported
     * (`00000000-0000-0000-0000-000000000000`).
     *
     * Generated from protobuf field <code>string request_id = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $request_id = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The resource name of the reseller's customer account in which to create the
     *           entitlement.
     *           Parent uses the format: accounts/{account_id}/customers/{customer_id}
     *     @type \Google\Cloud\Channel\V1\Entitlement $entitlement
     *           Required. The entitlement to create.
     *     @type string $request_id
     *           Optional. You can specify an optional unique request ID, and if you need to retry
     *           your request, the server will know to ignore the request if it's complete.
     *           For example, you make an initial request and the request times out. If you
     *           make the request again with the same request ID, the server can check if
     *           it received the original operation with the same request ID. If it did, it
     *           will ignore the second request.
     *           The request ID must be a valid [UUID](https://tools.ietf.org/html/rfc4122)
     *           with the exception that zero UUID is not supported
     *           (`00000000-0000-0000-0000-000000000000`).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Channel\V1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The resource name of the reseller's customer account in which to create the
     * entitlement.
     * Parent uses the format: accounts/{account_id}/customers/{customer_id}
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The resource name of the reseller's customer account in which to create the
     * entitlement.
     * Parent uses the format: accounts/{account_id}/customers/{customer_id}
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
     * Required. The entitlement to create.
     *
     * Generated from protobuf field <code>.google.cloud.channel.v1.Entitlement entitlement = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Channel\V1\Entitlement|null
     */
    public function getEntitlement()
    {
        return $this->entitlement;
    }

    public function hasEntitlement()
    {
        return isset($this->entitlement);
    }

    public function clearEntitlement()
    {
        unset($this->entitlement);
    }

    /**
     * Required. The entitlement to create.
     *
     * Generated from protobuf field <code>.google.cloud.channel.v1.Entitlement entitlement = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Channel\V1\Entitlement $var
     * @return $this
     */
    public function setEntitlement($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Channel\V1\Entitlement::class);
        $this->entitlement = $var;

        return $this;
    }

    /**
     * Optional. You can specify an optional unique request ID, and if you need to retry
     * your request, the server will know to ignore the request if it's complete.
     * For example, you make an initial request and the request times out. If you
     * make the request again with the same request ID, the server can check if
     * it received the original operation with the same request ID. If it did, it
     * will ignore the second request.
     * The request ID must be a valid [UUID](https://tools.ietf.org/html/rfc4122)
     * with the exception that zero UUID is not supported
     * (`00000000-0000-0000-0000-000000000000`).
     *
     * Generated from protobuf field <code>string request_id = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * Optional. You can specify an optional unique request ID, and if you need to retry
     * your request, the server will know to ignore the request if it's complete.
     * For example, you make an initial request and the request times out. If you
     * make the request again with the same request ID, the server can check if
     * it received the original operation with the same request ID. If it did, it
     * will ignore the second request.
     * The request ID must be a valid [UUID](https://tools.ietf.org/html/rfc4122)
     * with the exception that zero UUID is not supported
     * (`00000000-0000-0000-0000-000000000000`).
     *
     * Generated from protobuf field <code>string request_id = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setRequestId($var)
    {
        GPBUtil::checkString($var, True);
        $this->request_id = $var;

        return $this;
    }

}

