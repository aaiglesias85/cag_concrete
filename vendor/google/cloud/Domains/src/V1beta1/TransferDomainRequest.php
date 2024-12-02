<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/domains/v1beta1/domains.proto

namespace Google\Cloud\Domains\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request for the `TransferDomain` method.
 *
 * Generated from protobuf message <code>google.cloud.domains.v1beta1.TransferDomainRequest</code>
 */
class TransferDomainRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent resource of the `Registration`. Must be in the
     * format `projects/&#42;&#47;locations/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The complete `Registration` resource to be created.
     * You can leave `registration.dns_settings` unset to import the
     * domain's current DNS configuration from its current registrar. Use this
     * option only if you are sure that the domain's current DNS service
     * does not cease upon transfer, as is often the case for DNS services
     * provided for free by the registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.Registration registration = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $registration = null;
    /**
     * The list of contact notices that you acknowledge. The notices
     * needed here depend on the values specified in
     * `registration.contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 3;</code>
     */
    private $contact_notices;
    /**
     * Required. Acknowledgement of the price to transfer or renew the domain for one year.
     * Call `RetrieveTransferParameters` to obtain the price, which you must
     * acknowledge.
     *
     * Generated from protobuf field <code>.google.type.Money yearly_price = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $yearly_price = null;
    /**
     * The domain's transfer authorization code. You can obtain this from the
     * domain's current registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.AuthorizationCode authorization_code = 5;</code>
     */
    private $authorization_code = null;
    /**
     * Validate the request without actually transferring the domain.
     *
     * Generated from protobuf field <code>bool validate_only = 6;</code>
     */
    private $validate_only = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent resource of the `Registration`. Must be in the
     *           format `projects/&#42;&#47;locations/&#42;`.
     *     @type \Google\Cloud\Domains\V1beta1\Registration $registration
     *           Required. The complete `Registration` resource to be created.
     *           You can leave `registration.dns_settings` unset to import the
     *           domain's current DNS configuration from its current registrar. Use this
     *           option only if you are sure that the domain's current DNS service
     *           does not cease upon transfer, as is often the case for DNS services
     *           provided for free by the registrar.
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $contact_notices
     *           The list of contact notices that you acknowledge. The notices
     *           needed here depend on the values specified in
     *           `registration.contact_settings`.
     *     @type \Google\Type\Money $yearly_price
     *           Required. Acknowledgement of the price to transfer or renew the domain for one year.
     *           Call `RetrieveTransferParameters` to obtain the price, which you must
     *           acknowledge.
     *     @type \Google\Cloud\Domains\V1beta1\AuthorizationCode $authorization_code
     *           The domain's transfer authorization code. You can obtain this from the
     *           domain's current registrar.
     *     @type bool $validate_only
     *           Validate the request without actually transferring the domain.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Domains\V1Beta1\Domains::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent resource of the `Registration`. Must be in the
     * format `projects/&#42;&#47;locations/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent resource of the `Registration`. Must be in the
     * format `projects/&#42;&#47;locations/&#42;`.
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
     * Required. The complete `Registration` resource to be created.
     * You can leave `registration.dns_settings` unset to import the
     * domain's current DNS configuration from its current registrar. Use this
     * option only if you are sure that the domain's current DNS service
     * does not cease upon transfer, as is often the case for DNS services
     * provided for free by the registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.Registration registration = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Domains\V1beta1\Registration|null
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    public function hasRegistration()
    {
        return isset($this->registration);
    }

    public function clearRegistration()
    {
        unset($this->registration);
    }

    /**
     * Required. The complete `Registration` resource to be created.
     * You can leave `registration.dns_settings` unset to import the
     * domain's current DNS configuration from its current registrar. Use this
     * option only if you are sure that the domain's current DNS service
     * does not cease upon transfer, as is often the case for DNS services
     * provided for free by the registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.Registration registration = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Domains\V1beta1\Registration $var
     * @return $this
     */
    public function setRegistration($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Domains\V1beta1\Registration::class);
        $this->registration = $var;

        return $this;
    }

    /**
     * The list of contact notices that you acknowledge. The notices
     * needed here depend on the values specified in
     * `registration.contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getContactNotices()
    {
        return $this->contact_notices;
    }

    /**
     * The list of contact notices that you acknowledge. The notices
     * needed here depend on the values specified in
     * `registration.contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 3;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setContactNotices($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::ENUM, \Google\Cloud\Domains\V1beta1\ContactNotice::class);
        $this->contact_notices = $arr;

        return $this;
    }

    /**
     * Required. Acknowledgement of the price to transfer or renew the domain for one year.
     * Call `RetrieveTransferParameters` to obtain the price, which you must
     * acknowledge.
     *
     * Generated from protobuf field <code>.google.type.Money yearly_price = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Type\Money|null
     */
    public function getYearlyPrice()
    {
        return $this->yearly_price;
    }

    public function hasYearlyPrice()
    {
        return isset($this->yearly_price);
    }

    public function clearYearlyPrice()
    {
        unset($this->yearly_price);
    }

    /**
     * Required. Acknowledgement of the price to transfer or renew the domain for one year.
     * Call `RetrieveTransferParameters` to obtain the price, which you must
     * acknowledge.
     *
     * Generated from protobuf field <code>.google.type.Money yearly_price = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Type\Money $var
     * @return $this
     */
    public function setYearlyPrice($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Money::class);
        $this->yearly_price = $var;

        return $this;
    }

    /**
     * The domain's transfer authorization code. You can obtain this from the
     * domain's current registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.AuthorizationCode authorization_code = 5;</code>
     * @return \Google\Cloud\Domains\V1beta1\AuthorizationCode|null
     */
    public function getAuthorizationCode()
    {
        return $this->authorization_code;
    }

    public function hasAuthorizationCode()
    {
        return isset($this->authorization_code);
    }

    public function clearAuthorizationCode()
    {
        unset($this->authorization_code);
    }

    /**
     * The domain's transfer authorization code. You can obtain this from the
     * domain's current registrar.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.AuthorizationCode authorization_code = 5;</code>
     * @param \Google\Cloud\Domains\V1beta1\AuthorizationCode $var
     * @return $this
     */
    public function setAuthorizationCode($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Domains\V1beta1\AuthorizationCode::class);
        $this->authorization_code = $var;

        return $this;
    }

    /**
     * Validate the request without actually transferring the domain.
     *
     * Generated from protobuf field <code>bool validate_only = 6;</code>
     * @return bool
     */
    public function getValidateOnly()
    {
        return $this->validate_only;
    }

    /**
     * Validate the request without actually transferring the domain.
     *
     * Generated from protobuf field <code>bool validate_only = 6;</code>
     * @param bool $var
     * @return $this
     */
    public function setValidateOnly($var)
    {
        GPBUtil::checkBool($var);
        $this->validate_only = $var;

        return $this;
    }

}

