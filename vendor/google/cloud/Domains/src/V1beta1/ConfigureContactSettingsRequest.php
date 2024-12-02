<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/domains/v1beta1/domains.proto

namespace Google\Cloud\Domains\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request for the `ConfigureContactSettings` method.
 *
 * Generated from protobuf message <code>google.cloud.domains.v1beta1.ConfigureContactSettingsRequest</code>
 */
class ConfigureContactSettingsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the `Registration` whose contact settings are being updated,
     * in the format `projects/&#42;&#47;locations/&#42;&#47;registrations/&#42;`.
     *
     * Generated from protobuf field <code>string registration = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $registration = '';
    /**
     * Fields of the `ContactSettings` to update.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.ContactSettings contact_settings = 2;</code>
     */
    private $contact_settings = null;
    /**
     * Required. The field mask describing which fields to update as a comma-separated list.
     * For example, if only the registrant contact is being updated, the
     * `update_mask` is `"registrant_contact"`.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $update_mask = null;
    /**
     * The list of contact notices that the caller acknowledges. The notices
     * needed here depend on the values specified in `contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 4;</code>
     */
    private $contact_notices;
    /**
     * Validate the request without actually updating the contact settings.
     *
     * Generated from protobuf field <code>bool validate_only = 5;</code>
     */
    private $validate_only = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $registration
     *           Required. The name of the `Registration` whose contact settings are being updated,
     *           in the format `projects/&#42;&#47;locations/&#42;&#47;registrations/&#42;`.
     *     @type \Google\Cloud\Domains\V1beta1\ContactSettings $contact_settings
     *           Fields of the `ContactSettings` to update.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Required. The field mask describing which fields to update as a comma-separated list.
     *           For example, if only the registrant contact is being updated, the
     *           `update_mask` is `"registrant_contact"`.
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $contact_notices
     *           The list of contact notices that the caller acknowledges. The notices
     *           needed here depend on the values specified in `contact_settings`.
     *     @type bool $validate_only
     *           Validate the request without actually updating the contact settings.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Domains\V1Beta1\Domains::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the `Registration` whose contact settings are being updated,
     * in the format `projects/&#42;&#47;locations/&#42;&#47;registrations/&#42;`.
     *
     * Generated from protobuf field <code>string registration = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Required. The name of the `Registration` whose contact settings are being updated,
     * in the format `projects/&#42;&#47;locations/&#42;&#47;registrations/&#42;`.
     *
     * Generated from protobuf field <code>string registration = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setRegistration($var)
    {
        GPBUtil::checkString($var, True);
        $this->registration = $var;

        return $this;
    }

    /**
     * Fields of the `ContactSettings` to update.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.ContactSettings contact_settings = 2;</code>
     * @return \Google\Cloud\Domains\V1beta1\ContactSettings|null
     */
    public function getContactSettings()
    {
        return $this->contact_settings;
    }

    public function hasContactSettings()
    {
        return isset($this->contact_settings);
    }

    public function clearContactSettings()
    {
        unset($this->contact_settings);
    }

    /**
     * Fields of the `ContactSettings` to update.
     *
     * Generated from protobuf field <code>.google.cloud.domains.v1beta1.ContactSettings contact_settings = 2;</code>
     * @param \Google\Cloud\Domains\V1beta1\ContactSettings $var
     * @return $this
     */
    public function setContactSettings($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Domains\V1beta1\ContactSettings::class);
        $this->contact_settings = $var;

        return $this;
    }

    /**
     * Required. The field mask describing which fields to update as a comma-separated list.
     * For example, if only the registrant contact is being updated, the
     * `update_mask` is `"registrant_contact"`.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\FieldMask|null
     */
    public function getUpdateMask()
    {
        return $this->update_mask;
    }

    public function hasUpdateMask()
    {
        return isset($this->update_mask);
    }

    public function clearUpdateMask()
    {
        unset($this->update_mask);
    }

    /**
     * Required. The field mask describing which fields to update as a comma-separated list.
     * For example, if only the registrant contact is being updated, the
     * `update_mask` is `"registrant_contact"`.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;

        return $this;
    }

    /**
     * The list of contact notices that the caller acknowledges. The notices
     * needed here depend on the values specified in `contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getContactNotices()
    {
        return $this->contact_notices;
    }

    /**
     * The list of contact notices that the caller acknowledges. The notices
     * needed here depend on the values specified in `contact_settings`.
     *
     * Generated from protobuf field <code>repeated .google.cloud.domains.v1beta1.ContactNotice contact_notices = 4;</code>
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
     * Validate the request without actually updating the contact settings.
     *
     * Generated from protobuf field <code>bool validate_only = 5;</code>
     * @return bool
     */
    public function getValidateOnly()
    {
        return $this->validate_only;
    }

    /**
     * Validate the request without actually updating the contact settings.
     *
     * Generated from protobuf field <code>bool validate_only = 5;</code>
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

