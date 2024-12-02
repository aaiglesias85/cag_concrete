<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/conversation_profile.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Defines the Automated Agent to connect to a conversation.
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.AutomatedAgentConfig</code>
 */
class AutomatedAgentConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. ID of the Dialogflow agent environment to use.
     * This project needs to either be the same project as the conversation or you
     * need to grant `service-<Conversation Project
     * Number>&#64;gcp-sa-dialogflow.iam.gserviceaccount.com` the `Dialogflow API
     * Service Agent` role in this project.
     * - For ES agents, use format: `projects/<Project ID>/locations/<Location
     * ID>/agent/environments/<Environment ID or '-'>`. If environment is not
     * specified, the default `draft` environment is used. Refer to
     * [DetectIntentRequest](https://cloud.google.com/dialogflow/docs/reference/rpc/google.cloud.dialogflow.v2#google.cloud.dialogflow.v2.DetectIntentRequest)
     * for more details.
     * - For CX agents, use format `projects/<Project ID>/locations/<Location
     * ID>/agents/<Agent ID>/environments/<Environment ID
     * or '-'>`. If environment is not specified, the default `draft` environment
     * is used.
     *
     * Generated from protobuf field <code>string agent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $agent = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $agent
     *           Required. ID of the Dialogflow agent environment to use.
     *           This project needs to either be the same project as the conversation or you
     *           need to grant `service-<Conversation Project
     *           Number>&#64;gcp-sa-dialogflow.iam.gserviceaccount.com` the `Dialogflow API
     *           Service Agent` role in this project.
     *           - For ES agents, use format: `projects/<Project ID>/locations/<Location
     *           ID>/agent/environments/<Environment ID or '-'>`. If environment is not
     *           specified, the default `draft` environment is used. Refer to
     *           [DetectIntentRequest](https://cloud.google.com/dialogflow/docs/reference/rpc/google.cloud.dialogflow.v2#google.cloud.dialogflow.v2.DetectIntentRequest)
     *           for more details.
     *           - For CX agents, use format `projects/<Project ID>/locations/<Location
     *           ID>/agents/<Agent ID>/environments/<Environment ID
     *           or '-'>`. If environment is not specified, the default `draft` environment
     *           is used.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\ConversationProfile::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. ID of the Dialogflow agent environment to use.
     * This project needs to either be the same project as the conversation or you
     * need to grant `service-<Conversation Project
     * Number>&#64;gcp-sa-dialogflow.iam.gserviceaccount.com` the `Dialogflow API
     * Service Agent` role in this project.
     * - For ES agents, use format: `projects/<Project ID>/locations/<Location
     * ID>/agent/environments/<Environment ID or '-'>`. If environment is not
     * specified, the default `draft` environment is used. Refer to
     * [DetectIntentRequest](https://cloud.google.com/dialogflow/docs/reference/rpc/google.cloud.dialogflow.v2#google.cloud.dialogflow.v2.DetectIntentRequest)
     * for more details.
     * - For CX agents, use format `projects/<Project ID>/locations/<Location
     * ID>/agents/<Agent ID>/environments/<Environment ID
     * or '-'>`. If environment is not specified, the default `draft` environment
     * is used.
     *
     * Generated from protobuf field <code>string agent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Required. ID of the Dialogflow agent environment to use.
     * This project needs to either be the same project as the conversation or you
     * need to grant `service-<Conversation Project
     * Number>&#64;gcp-sa-dialogflow.iam.gserviceaccount.com` the `Dialogflow API
     * Service Agent` role in this project.
     * - For ES agents, use format: `projects/<Project ID>/locations/<Location
     * ID>/agent/environments/<Environment ID or '-'>`. If environment is not
     * specified, the default `draft` environment is used. Refer to
     * [DetectIntentRequest](https://cloud.google.com/dialogflow/docs/reference/rpc/google.cloud.dialogflow.v2#google.cloud.dialogflow.v2.DetectIntentRequest)
     * for more details.
     * - For CX agents, use format `projects/<Project ID>/locations/<Location
     * ID>/agents/<Agent ID>/environments/<Environment ID
     * or '-'>`. If environment is not specified, the default `draft` environment
     * is used.
     *
     * Generated from protobuf field <code>string agent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setAgent($var)
    {
        GPBUtil::checkString($var, True);
        $this->agent = $var;

        return $this;
    }

}

