<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for ListInspectTemplates.
 *
 * Generated from protobuf message <code>google.privacy.dlp.v2.ListInspectTemplatesResponse</code>
 */
class ListInspectTemplatesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * List of inspectTemplates, up to page_size in ListInspectTemplatesRequest.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InspectTemplate inspect_templates = 1;</code>
     */
    private $inspect_templates;
    /**
     * If the next page is available then the next page token to be used
     * in following ListInspectTemplates request.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dlp\V2\InspectTemplate[]|\Google\Protobuf\Internal\RepeatedField $inspect_templates
     *           List of inspectTemplates, up to page_size in ListInspectTemplatesRequest.
     *     @type string $next_page_token
     *           If the next page is available then the next page token to be used
     *           in following ListInspectTemplates request.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Privacy\Dlp\V2\Dlp::initOnce();
        parent::__construct($data);
    }

    /**
     * List of inspectTemplates, up to page_size in ListInspectTemplatesRequest.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InspectTemplate inspect_templates = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInspectTemplates()
    {
        return $this->inspect_templates;
    }

    /**
     * List of inspectTemplates, up to page_size in ListInspectTemplatesRequest.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.InspectTemplate inspect_templates = 1;</code>
     * @param \Google\Cloud\Dlp\V2\InspectTemplate[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInspectTemplates($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dlp\V2\InspectTemplate::class);
        $this->inspect_templates = $arr;

        return $this;
    }

    /**
     * If the next page is available then the next page token to be used
     * in following ListInspectTemplates request.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * If the next page is available then the next page token to be used
     * in following ListInspectTemplates request.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}

