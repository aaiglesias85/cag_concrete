<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/clouddms/v1/clouddms.proto

namespace Google\Cloud\CloudDms\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for 'CreateConnectionProfile' request.
 *
 * Generated from protobuf message <code>google.cloud.clouddms.v1.CreateConnectionProfileRequest</code>
 */
class CreateConnectionProfileRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent, which owns this collection of connection profiles.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The connection profile identifier.
     *
     * Generated from protobuf field <code>string connection_profile_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $connection_profile_id = '';
    /**
     * Required. The create request body including the connection profile data
     *
     * Generated from protobuf field <code>.google.cloud.clouddms.v1.ConnectionProfile connection_profile = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $connection_profile = null;
    /**
     * A unique id used to identify the request. If the server receives two
     * requests with the same id, then the second request will be ignored.
     * It is recommended to always set this value to a UUID.
     * The id must contain only letters (a-z, A-Z), numbers (0-9), underscores
     * (_), and hyphens (-). The maximum length is 40 characters.
     *
     * Generated from protobuf field <code>string request_id = 4;</code>
     */
    private $request_id = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent, which owns this collection of connection profiles.
     *     @type string $connection_profile_id
     *           Required. The connection profile identifier.
     *     @type \Google\Cloud\CloudDms\V1\ConnectionProfile $connection_profile
     *           Required. The create request body including the connection profile data
     *     @type string $request_id
     *           A unique id used to identify the request. If the server receives two
     *           requests with the same id, then the second request will be ignored.
     *           It is recommended to always set this value to a UUID.
     *           The id must contain only letters (a-z, A-Z), numbers (0-9), underscores
     *           (_), and hyphens (-). The maximum length is 40 characters.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Clouddms\V1\Clouddms::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent, which owns this collection of connection profiles.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent, which owns this collection of connection profiles.
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
     * Required. The connection profile identifier.
     *
     * Generated from protobuf field <code>string connection_profile_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getConnectionProfileId()
    {
        return $this->connection_profile_id;
    }

    /**
     * Required. The connection profile identifier.
     *
     * Generated from protobuf field <code>string connection_profile_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setConnectionProfileId($var)
    {
        GPBUtil::checkString($var, True);
        $this->connection_profile_id = $var;

        return $this;
    }

    /**
     * Required. The create request body including the connection profile data
     *
     * Generated from protobuf field <code>.google.cloud.clouddms.v1.ConnectionProfile connection_profile = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\CloudDms\V1\ConnectionProfile|null
     */
    public function getConnectionProfile()
    {
        return $this->connection_profile;
    }

    public function hasConnectionProfile()
    {
        return isset($this->connection_profile);
    }

    public function clearConnectionProfile()
    {
        unset($this->connection_profile);
    }

    /**
     * Required. The create request body including the connection profile data
     *
     * Generated from protobuf field <code>.google.cloud.clouddms.v1.ConnectionProfile connection_profile = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\CloudDms\V1\ConnectionProfile $var
     * @return $this
     */
    public function setConnectionProfile($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\CloudDms\V1\ConnectionProfile::class);
        $this->connection_profile = $var;

        return $this;
    }

    /**
     * A unique id used to identify the request. If the server receives two
     * requests with the same id, then the second request will be ignored.
     * It is recommended to always set this value to a UUID.
     * The id must contain only letters (a-z, A-Z), numbers (0-9), underscores
     * (_), and hyphens (-). The maximum length is 40 characters.
     *
     * Generated from protobuf field <code>string request_id = 4;</code>
     * @return string
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * A unique id used to identify the request. If the server receives two
     * requests with the same id, then the second request will be ignored.
     * It is recommended to always set this value to a UUID.
     * The id must contain only letters (a-z, A-Z), numbers (0-9), underscores
     * (_), and hyphens (-). The maximum length is 40 characters.
     *
     * Generated from protobuf field <code>string request_id = 4;</code>
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
