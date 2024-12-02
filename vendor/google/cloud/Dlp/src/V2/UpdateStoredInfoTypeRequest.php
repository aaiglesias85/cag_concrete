<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for UpdateStoredInfoType.
 *
 * Generated from protobuf message <code>google.privacy.dlp.v2.UpdateStoredInfoTypeRequest</code>
 */
class UpdateStoredInfoTypeRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Resource name of organization and storedInfoType to be updated, for
     * example `organizations/433245324/storedInfoTypes/432452342` or
     * projects/project-id/storedInfoTypes/432452342.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $name = '';
    /**
     * Updated configuration for the storedInfoType. If not provided, a new
     * version of the storedInfoType will be created with the existing
     * configuration.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.StoredInfoTypeConfig config = 2;</code>
     */
    private $config = null;
    /**
     * Mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3;</code>
     */
    private $update_mask = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Required. Resource name of organization and storedInfoType to be updated, for
     *           example `organizations/433245324/storedInfoTypes/432452342` or
     *           projects/project-id/storedInfoTypes/432452342.
     *     @type \Google\Cloud\Dlp\V2\StoredInfoTypeConfig $config
     *           Updated configuration for the storedInfoType. If not provided, a new
     *           version of the storedInfoType will be created with the existing
     *           configuration.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Mask to control which fields get updated.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Privacy\Dlp\V2\Dlp::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Resource name of organization and storedInfoType to be updated, for
     * example `organizations/433245324/storedInfoTypes/432452342` or
     * projects/project-id/storedInfoTypes/432452342.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Required. Resource name of organization and storedInfoType to be updated, for
     * example `organizations/433245324/storedInfoTypes/432452342` or
     * projects/project-id/storedInfoTypes/432452342.
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

    /**
     * Updated configuration for the storedInfoType. If not provided, a new
     * version of the storedInfoType will be created with the existing
     * configuration.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.StoredInfoTypeConfig config = 2;</code>
     * @return \Google\Cloud\Dlp\V2\StoredInfoTypeConfig|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function hasConfig()
    {
        return isset($this->config);
    }

    public function clearConfig()
    {
        unset($this->config);
    }

    /**
     * Updated configuration for the storedInfoType. If not provided, a new
     * version of the storedInfoType will be created with the existing
     * configuration.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.StoredInfoTypeConfig config = 2;</code>
     * @param \Google\Cloud\Dlp\V2\StoredInfoTypeConfig $var
     * @return $this
     */
    public function setConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dlp\V2\StoredInfoTypeConfig::class);
        $this->config = $var;

        return $this;
    }

    /**
     * Mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3;</code>
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
     * Mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3;</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;

        return $this;
    }

}

