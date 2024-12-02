<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/servicedirectory/v1beta1/registration_service.proto

namespace Google\Cloud\ServiceDirectory\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request message for [RegistrationService.DeleteService][google.cloud.servicedirectory.v1beta1.RegistrationService.DeleteService].
 *
 * Generated from protobuf message <code>google.cloud.servicedirectory.v1beta1.DeleteServiceRequest</code>
 */
class DeleteServiceRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the service to delete.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Required. The name of the service to delete.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Servicedirectory\V1Beta1\RegistrationService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the service to delete.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Required. The name of the service to delete.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

}

