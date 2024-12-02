<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request to search for potentially sensitive info in a ContentItem.
 *
 * Generated from protobuf message <code>google.privacy.dlp.v2.InspectContentRequest</code>
 */
class InspectContentRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Parent resource name.
     * The format of this value varies depending on whether you have [specified a
     * processing
     * location](https://cloud.google.com/dlp/docs/specifying-location):
     * + Projects scope, location specified:<br/>
     *   `projects/`<var>PROJECT_ID</var>`/locations/`<var>LOCATION_ID</var>
     * + Projects scope, no location specified (defaults to global):<br/>
     *   `projects/`<var>PROJECT_ID</var>
     * The following example `parent` string specifies a parent project with the
     * identifier `example-project`, and specifies the `europe-west3` location
     * for processing data:
     *     parent=projects/example-project/locations/europe-west3
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Configuration for the inspector. What specified here will override
     * the template referenced by the inspect_template_name argument.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig inspect_config = 2;</code>
     */
    private $inspect_config = null;
    /**
     * The item to inspect.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.ContentItem item = 3;</code>
     */
    private $item = null;
    /**
     * Template to use. Any configuration directly specified in
     * inspect_config will override those set in the template. Singular fields
     * that are set in this request will replace their corresponding fields in the
     * template. Repeated fields are appended. Singular sub-messages and groups
     * are recursively merged.
     *
     * Generated from protobuf field <code>string inspect_template_name = 4;</code>
     */
    private $inspect_template_name = '';
    /**
     * Deprecated. This field has no effect.
     *
     * Generated from protobuf field <code>string location_id = 5;</code>
     */
    private $location_id = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Parent resource name.
     *           The format of this value varies depending on whether you have [specified a
     *           processing
     *           location](https://cloud.google.com/dlp/docs/specifying-location):
     *           + Projects scope, location specified:<br/>
     *             `projects/`<var>PROJECT_ID</var>`/locations/`<var>LOCATION_ID</var>
     *           + Projects scope, no location specified (defaults to global):<br/>
     *             `projects/`<var>PROJECT_ID</var>
     *           The following example `parent` string specifies a parent project with the
     *           identifier `example-project`, and specifies the `europe-west3` location
     *           for processing data:
     *               parent=projects/example-project/locations/europe-west3
     *     @type \Google\Cloud\Dlp\V2\InspectConfig $inspect_config
     *           Configuration for the inspector. What specified here will override
     *           the template referenced by the inspect_template_name argument.
     *     @type \Google\Cloud\Dlp\V2\ContentItem $item
     *           The item to inspect.
     *     @type string $inspect_template_name
     *           Template to use. Any configuration directly specified in
     *           inspect_config will override those set in the template. Singular fields
     *           that are set in this request will replace their corresponding fields in the
     *           template. Repeated fields are appended. Singular sub-messages and groups
     *           are recursively merged.
     *     @type string $location_id
     *           Deprecated. This field has no effect.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Privacy\Dlp\V2\Dlp::initOnce();
        parent::__construct($data);
    }

    /**
     * Parent resource name.
     * The format of this value varies depending on whether you have [specified a
     * processing
     * location](https://cloud.google.com/dlp/docs/specifying-location):
     * + Projects scope, location specified:<br/>
     *   `projects/`<var>PROJECT_ID</var>`/locations/`<var>LOCATION_ID</var>
     * + Projects scope, no location specified (defaults to global):<br/>
     *   `projects/`<var>PROJECT_ID</var>
     * The following example `parent` string specifies a parent project with the
     * identifier `example-project`, and specifies the `europe-west3` location
     * for processing data:
     *     parent=projects/example-project/locations/europe-west3
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Parent resource name.
     * The format of this value varies depending on whether you have [specified a
     * processing
     * location](https://cloud.google.com/dlp/docs/specifying-location):
     * + Projects scope, location specified:<br/>
     *   `projects/`<var>PROJECT_ID</var>`/locations/`<var>LOCATION_ID</var>
     * + Projects scope, no location specified (defaults to global):<br/>
     *   `projects/`<var>PROJECT_ID</var>
     * The following example `parent` string specifies a parent project with the
     * identifier `example-project`, and specifies the `europe-west3` location
     * for processing data:
     *     parent=projects/example-project/locations/europe-west3
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.resource_reference) = {</code>
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
     * Configuration for the inspector. What specified here will override
     * the template referenced by the inspect_template_name argument.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig inspect_config = 2;</code>
     * @return \Google\Cloud\Dlp\V2\InspectConfig|null
     */
    public function getInspectConfig()
    {
        return $this->inspect_config;
    }

    public function hasInspectConfig()
    {
        return isset($this->inspect_config);
    }

    public function clearInspectConfig()
    {
        unset($this->inspect_config);
    }

    /**
     * Configuration for the inspector. What specified here will override
     * the template referenced by the inspect_template_name argument.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.InspectConfig inspect_config = 2;</code>
     * @param \Google\Cloud\Dlp\V2\InspectConfig $var
     * @return $this
     */
    public function setInspectConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dlp\V2\InspectConfig::class);
        $this->inspect_config = $var;

        return $this;
    }

    /**
     * The item to inspect.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.ContentItem item = 3;</code>
     * @return \Google\Cloud\Dlp\V2\ContentItem|null
     */
    public function getItem()
    {
        return $this->item;
    }

    public function hasItem()
    {
        return isset($this->item);
    }

    public function clearItem()
    {
        unset($this->item);
    }

    /**
     * The item to inspect.
     *
     * Generated from protobuf field <code>.google.privacy.dlp.v2.ContentItem item = 3;</code>
     * @param \Google\Cloud\Dlp\V2\ContentItem $var
     * @return $this
     */
    public function setItem($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dlp\V2\ContentItem::class);
        $this->item = $var;

        return $this;
    }

    /**
     * Template to use. Any configuration directly specified in
     * inspect_config will override those set in the template. Singular fields
     * that are set in this request will replace their corresponding fields in the
     * template. Repeated fields are appended. Singular sub-messages and groups
     * are recursively merged.
     *
     * Generated from protobuf field <code>string inspect_template_name = 4;</code>
     * @return string
     */
    public function getInspectTemplateName()
    {
        return $this->inspect_template_name;
    }

    /**
     * Template to use. Any configuration directly specified in
     * inspect_config will override those set in the template. Singular fields
     * that are set in this request will replace their corresponding fields in the
     * template. Repeated fields are appended. Singular sub-messages and groups
     * are recursively merged.
     *
     * Generated from protobuf field <code>string inspect_template_name = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setInspectTemplateName($var)
    {
        GPBUtil::checkString($var, True);
        $this->inspect_template_name = $var;

        return $this;
    }

    /**
     * Deprecated. This field has no effect.
     *
     * Generated from protobuf field <code>string location_id = 5;</code>
     * @return string
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Deprecated. This field has no effect.
     *
     * Generated from protobuf field <code>string location_id = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setLocationId($var)
    {
        GPBUtil::checkString($var, True);
        $this->location_id = $var;

        return $this;
    }

}

