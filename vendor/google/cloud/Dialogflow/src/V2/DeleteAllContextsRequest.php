<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/context.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request message for [Contexts.DeleteAllContexts][google.cloud.dialogflow.v2.Contexts.DeleteAllContexts].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.DeleteAllContextsRequest</code>
 */
class DeleteAllContextsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the session to delete all contexts from. Format:
     * `projects/<Project ID>/agent/sessions/<Session ID>` or `projects/<Project
     * ID>/agent/environments/<Environment ID>/users/<User ID>/sessions/<Session
     * ID>`.
     * If `Environment ID` is not specified we assume default 'draft' environment.
     * If `User ID` is not specified, we assume default '-' user.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The name of the session to delete all contexts from. Format:
     *           `projects/<Project ID>/agent/sessions/<Session ID>` or `projects/<Project
     *           ID>/agent/environments/<Environment ID>/users/<User ID>/sessions/<Session
     *           ID>`.
     *           If `Environment ID` is not specified we assume default 'draft' environment.
     *           If `User ID` is not specified, we assume default '-' user.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Context::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the session to delete all contexts from. Format:
     * `projects/<Project ID>/agent/sessions/<Session ID>` or `projects/<Project
     * ID>/agent/environments/<Environment ID>/users/<User ID>/sessions/<Session
     * ID>`.
     * If `Environment ID` is not specified we assume default 'draft' environment.
     * If `User ID` is not specified, we assume default '-' user.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The name of the session to delete all contexts from. Format:
     * `projects/<Project ID>/agent/sessions/<Session ID>` or `projects/<Project
     * ID>/agent/environments/<Environment ID>/users/<User ID>/sessions/<Session
     * ID>`.
     * If `Environment ID` is not specified we assume default 'draft' environment.
     * If `User ID` is not specified, we assume default '-' user.
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

}

