<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/securitycenter/v1p1beta1/securitycenter_service.proto

namespace Google\Cloud\SecurityCenter\V1p1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for creating a source.
 *
 * Generated from protobuf message <code>google.cloud.securitycenter.v1p1beta1.CreateSourceRequest</code>
 */
class CreateSourceRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Resource name of the new source's parent. Its format should be
     * "organizations/[organization_id]".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. The Source being created, only the display_name and description will be
     * used. All other fields will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.securitycenter.v1p1beta1.Source source = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $source = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. Resource name of the new source's parent. Its format should be
     *           "organizations/[organization_id]".
     *     @type \Google\Cloud\SecurityCenter\V1p1beta1\Source $source
     *           Required. The Source being created, only the display_name and description will be
     *           used. All other fields will be ignored.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Securitycenter\V1P1Beta1\SecuritycenterService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Resource name of the new source's parent. Its format should be
     * "organizations/[organization_id]".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. Resource name of the new source's parent. Its format should be
     * "organizations/[organization_id]".
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
     * Required. The Source being created, only the display_name and description will be
     * used. All other fields will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.securitycenter.v1p1beta1.Source source = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\SecurityCenter\V1p1beta1\Source|null
     */
    public function getSource()
    {
        return $this->source;
    }

    public function hasSource()
    {
        return isset($this->source);
    }

    public function clearSource()
    {
        unset($this->source);
    }

    /**
     * Required. The Source being created, only the display_name and description will be
     * used. All other fields will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.securitycenter.v1p1beta1.Source source = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\SecurityCenter\V1p1beta1\Source $var
     * @return $this
     */
    public function setSource($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\SecurityCenter\V1p1beta1\Source::class);
        $this->source = $var;

        return $this;
    }

}

