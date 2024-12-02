<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/environment.proto

namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request message for [Environments.UpdateEnvironment][google.cloud.dialogflow.v2.Environments.UpdateEnvironment].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.UpdateEnvironmentRequest</code>
 */
class UpdateEnvironmentRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The environment to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Environment environment = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $environment = null;
    /**
     * Required. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $update_mask = null;
    /**
     * Optional. This field is used to prevent accidental overwrite of the default
     * environment, which is an operation that cannot be undone. To confirm that
     * the caller desires this overwrite, this field must be explicitly set to
     * true when updating the default environment (environment ID = `-`).
     *
     * Generated from protobuf field <code>bool allow_load_to_draft_and_discard_changes = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $allow_load_to_draft_and_discard_changes = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dialogflow\V2\Environment $environment
     *           Required. The environment to update.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Required. The mask to control which fields get updated.
     *     @type bool $allow_load_to_draft_and_discard_changes
     *           Optional. This field is used to prevent accidental overwrite of the default
     *           environment, which is an operation that cannot be undone. To confirm that
     *           the caller desires this overwrite, this field must be explicitly set to
     *           true when updating the default environment (environment ID = `-`).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Environment::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The environment to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Environment environment = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Dialogflow\V2\Environment|null
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function hasEnvironment()
    {
        return isset($this->environment);
    }

    public function clearEnvironment()
    {
        unset($this->environment);
    }

    /**
     * Required. The environment to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Environment environment = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Dialogflow\V2\Environment $var
     * @return $this
     */
    public function setEnvironment($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dialogflow\V2\Environment::class);
        $this->environment = $var;

        return $this;
    }

    /**
     * Required. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = REQUIRED];</code>
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
     * Required. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = REQUIRED];</code>
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
     * Optional. This field is used to prevent accidental overwrite of the default
     * environment, which is an operation that cannot be undone. To confirm that
     * the caller desires this overwrite, this field must be explicitly set to
     * true when updating the default environment (environment ID = `-`).
     *
     * Generated from protobuf field <code>bool allow_load_to_draft_and_discard_changes = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return bool
     */
    public function getAllowLoadToDraftAndDiscardChanges()
    {
        return $this->allow_load_to_draft_and_discard_changes;
    }

    /**
     * Optional. This field is used to prevent accidental overwrite of the default
     * environment, which is an operation that cannot be undone. To confirm that
     * the caller desires this overwrite, this field must be explicitly set to
     * true when updating the default environment (environment ID = `-`).
     *
     * Generated from protobuf field <code>bool allow_load_to_draft_and_discard_changes = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param bool $var
     * @return $this
     */
    public function setAllowLoadToDraftAndDiscardChanges($var)
    {
        GPBUtil::checkBool($var);
        $this->allow_load_to_draft_and_discard_changes = $var;

        return $this;
    }

}

