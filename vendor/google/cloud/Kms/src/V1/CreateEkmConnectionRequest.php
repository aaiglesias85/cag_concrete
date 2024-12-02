<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/kms/v1/ekm_service.proto

namespace Google\Cloud\Kms\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for [KeyManagementService.CreateEkmConnection][].
 *
 * Generated from protobuf message <code>google.cloud.kms.v1.CreateEkmConnectionRequest</code>
 */
class CreateEkmConnectionRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The resource name of the location associated with the
     * [EkmConnection][google.cloud.kms.v1.EkmConnection], in the format
     * `projects/&#42;&#47;locations/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. It must be unique within a location and match the regular
     * expression `[a-zA-Z0-9_-]{1,63}`.
     *
     * Generated from protobuf field <code>string ekm_connection_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $ekm_connection_id = '';
    /**
     * Required. An [EkmConnection][google.cloud.kms.v1.EkmConnection] with
     * initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.EkmConnection ekm_connection = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $ekm_connection = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The resource name of the location associated with the
     *           [EkmConnection][google.cloud.kms.v1.EkmConnection], in the format
     *           `projects/&#42;&#47;locations/&#42;`.
     *     @type string $ekm_connection_id
     *           Required. It must be unique within a location and match the regular
     *           expression `[a-zA-Z0-9_-]{1,63}`.
     *     @type \Google\Cloud\Kms\V1\EkmConnection $ekm_connection
     *           Required. An [EkmConnection][google.cloud.kms.v1.EkmConnection] with
     *           initial field values.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Kms\V1\EkmService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The resource name of the location associated with the
     * [EkmConnection][google.cloud.kms.v1.EkmConnection], in the format
     * `projects/&#42;&#47;locations/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The resource name of the location associated with the
     * [EkmConnection][google.cloud.kms.v1.EkmConnection], in the format
     * `projects/&#42;&#47;locations/&#42;`.
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
     * Required. It must be unique within a location and match the regular
     * expression `[a-zA-Z0-9_-]{1,63}`.
     *
     * Generated from protobuf field <code>string ekm_connection_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getEkmConnectionId()
    {
        return $this->ekm_connection_id;
    }

    /**
     * Required. It must be unique within a location and match the regular
     * expression `[a-zA-Z0-9_-]{1,63}`.
     *
     * Generated from protobuf field <code>string ekm_connection_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setEkmConnectionId($var)
    {
        GPBUtil::checkString($var, True);
        $this->ekm_connection_id = $var;

        return $this;
    }

    /**
     * Required. An [EkmConnection][google.cloud.kms.v1.EkmConnection] with
     * initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.EkmConnection ekm_connection = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Kms\V1\EkmConnection|null
     */
    public function getEkmConnection()
    {
        return $this->ekm_connection;
    }

    public function hasEkmConnection()
    {
        return isset($this->ekm_connection);
    }

    public function clearEkmConnection()
    {
        unset($this->ekm_connection);
    }

    /**
     * Required. An [EkmConnection][google.cloud.kms.v1.EkmConnection] with
     * initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.EkmConnection ekm_connection = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Kms\V1\EkmConnection $var
     * @return $this
     */
    public function setEkmConnection($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Kms\V1\EkmConnection::class);
        $this->ekm_connection = $var;

        return $this;
    }

}

