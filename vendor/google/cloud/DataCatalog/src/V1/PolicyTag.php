<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datacatalog/v1/policytagmanager.proto

namespace Google\Cloud\DataCatalog\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Denotes one policy tag in a taxonomy, for example, SSN.
 * Policy tags can be defined in a hierarchy. For example:
 * ```
 * + Geolocation
 *   + LatLong
 *   + City
 *   + ZipCode
 * ```
 * Where the "Geolocation" policy tag contains three children.
 *
 * Generated from protobuf message <code>google.cloud.datacatalog.v1.PolicyTag</code>
 */
class PolicyTag extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. Resource name of this policy tag in the URL format.
     * The policy tag manager generates unique taxonomy IDs and policy tag IDs.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Required. User-defined name of this policy tag.
     * The name can't start or end with spaces and must be unique within the
     * parent taxonomy, contain only Unicode letters, numbers, underscores, dashes
     * and spaces, and be at most 200 bytes long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $display_name = '';
    /**
     * Description of this policy tag. If not set, defaults to empty.
     * The description must contain only Unicode characters,
     * tabs, newlines, carriage returns and page breaks, and be at most 2000 bytes
     * long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     */
    private $description = '';
    /**
     * Resource name of this policy tag's parent policy tag. If empty, this is a
     * top level tag. If not set, defaults to an empty string.
     * For example, for the "LatLong" policy tag in the example above, this field
     * contains the resource name of the "Geolocation" policy tag, and, for
     * "Geolocation", this field is empty.
     *
     * Generated from protobuf field <code>string parent_policy_tag = 4;</code>
     */
    private $parent_policy_tag = '';
    /**
     * Output only. Resource names of child policy tags of this policy tag.
     *
     * Generated from protobuf field <code>repeated string child_policy_tags = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $child_policy_tags;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. Resource name of this policy tag in the URL format.
     *           The policy tag manager generates unique taxonomy IDs and policy tag IDs.
     *     @type string $display_name
     *           Required. User-defined name of this policy tag.
     *           The name can't start or end with spaces and must be unique within the
     *           parent taxonomy, contain only Unicode letters, numbers, underscores, dashes
     *           and spaces, and be at most 200 bytes long when encoded in UTF-8.
     *     @type string $description
     *           Description of this policy tag. If not set, defaults to empty.
     *           The description must contain only Unicode characters,
     *           tabs, newlines, carriage returns and page breaks, and be at most 2000 bytes
     *           long when encoded in UTF-8.
     *     @type string $parent_policy_tag
     *           Resource name of this policy tag's parent policy tag. If empty, this is a
     *           top level tag. If not set, defaults to an empty string.
     *           For example, for the "LatLong" policy tag in the example above, this field
     *           contains the resource name of the "Geolocation" policy tag, and, for
     *           "Geolocation", this field is empty.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $child_policy_tags
     *           Output only. Resource names of child policy tags of this policy tag.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datacatalog\V1\Policytagmanager::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. Resource name of this policy tag in the URL format.
     * The policy tag manager generates unique taxonomy IDs and policy tag IDs.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. Resource name of this policy tag in the URL format.
     * The policy tag manager generates unique taxonomy IDs and policy tag IDs.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Required. User-defined name of this policy tag.
     * The name can't start or end with spaces and must be unique within the
     * parent taxonomy, contain only Unicode letters, numbers, underscores, dashes
     * and spaces, and be at most 200 bytes long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Required. User-defined name of this policy tag.
     * The name can't start or end with spaces and must be unique within the
     * parent taxonomy, contain only Unicode letters, numbers, underscores, dashes
     * and spaces, and be at most 200 bytes long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string display_name = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * Description of this policy tag. If not set, defaults to empty.
     * The description must contain only Unicode characters,
     * tabs, newlines, carriage returns and page breaks, and be at most 2000 bytes
     * long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Description of this policy tag. If not set, defaults to empty.
     * The description must contain only Unicode characters,
     * tabs, newlines, carriage returns and page breaks, and be at most 2000 bytes
     * long when encoded in UTF-8.
     *
     * Generated from protobuf field <code>string description = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * Resource name of this policy tag's parent policy tag. If empty, this is a
     * top level tag. If not set, defaults to an empty string.
     * For example, for the "LatLong" policy tag in the example above, this field
     * contains the resource name of the "Geolocation" policy tag, and, for
     * "Geolocation", this field is empty.
     *
     * Generated from protobuf field <code>string parent_policy_tag = 4;</code>
     * @return string
     */
    public function getParentPolicyTag()
    {
        return $this->parent_policy_tag;
    }

    /**
     * Resource name of this policy tag's parent policy tag. If empty, this is a
     * top level tag. If not set, defaults to an empty string.
     * For example, for the "LatLong" policy tag in the example above, this field
     * contains the resource name of the "Geolocation" policy tag, and, for
     * "Geolocation", this field is empty.
     *
     * Generated from protobuf field <code>string parent_policy_tag = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setParentPolicyTag($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent_policy_tag = $var;

        return $this;
    }

    /**
     * Output only. Resource names of child policy tags of this policy tag.
     *
     * Generated from protobuf field <code>repeated string child_policy_tags = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getChildPolicyTags()
    {
        return $this->child_policy_tags;
    }

    /**
     * Output only. Resource names of child policy tags of this policy tag.
     *
     * Generated from protobuf field <code>repeated string child_policy_tags = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setChildPolicyTags($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->child_policy_tags = $arr;

        return $this;
    }

}

