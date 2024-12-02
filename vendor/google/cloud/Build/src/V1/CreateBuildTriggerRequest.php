<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/cloudbuild/v1/cloudbuild.proto

namespace Google\Cloud\Build\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request to create a new `BuildTrigger`.
 *
 * Generated from protobuf message <code>google.devtools.cloudbuild.v1.CreateBuildTriggerRequest</code>
 */
class CreateBuildTriggerRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. ID of the project for which to configure automatic builds.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $project_id = '';
    /**
     * Required. `BuildTrigger` to create.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildTrigger trigger = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $trigger = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $project_id
     *           Required. ID of the project for which to configure automatic builds.
     *     @type \Google\Cloud\Build\V1\BuildTrigger $trigger
     *           Required. `BuildTrigger` to create.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Cloudbuild\V1\Cloudbuild::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. ID of the project for which to configure automatic builds.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * Required. ID of the project for which to configure automatic builds.
     *
     * Generated from protobuf field <code>string project_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setProjectId($var)
    {
        GPBUtil::checkString($var, True);
        $this->project_id = $var;

        return $this;
    }

    /**
     * Required. `BuildTrigger` to create.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildTrigger trigger = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Build\V1\BuildTrigger|null
     */
    public function getTrigger()
    {
        return isset($this->trigger) ? $this->trigger : null;
    }

    public function hasTrigger()
    {
        return isset($this->trigger);
    }

    public function clearTrigger()
    {
        unset($this->trigger);
    }

    /**
     * Required. `BuildTrigger` to create.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildTrigger trigger = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Build\V1\BuildTrigger $var
     * @return $this
     */
    public function setTrigger($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Build\V1\BuildTrigger::class);
        $this->trigger = $var;

        return $this;
    }

}

