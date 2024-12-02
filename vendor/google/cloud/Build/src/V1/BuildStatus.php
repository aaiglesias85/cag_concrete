<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/build/v1/build_status.proto

namespace Google\Cloud\Build\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Status used for both invocation attempt and overall build completion.
 *
 * Generated from protobuf message <code>google.devtools.build.v1.BuildStatus</code>
 */
class BuildStatus extends \Google\Protobuf\Internal\Message
{
    /**
     * The end result.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildStatus.Result result = 1;</code>
     */
    private $result = 0;
    /**
     * Final invocation ID of the build, if there was one.
     * This field is only set on a status in BuildFinished event.
     *
     * Generated from protobuf field <code>string final_invocation_id = 3;</code>
     */
    private $final_invocation_id = '';
    /**
     * Build tool exit code. Integer value returned by the executed build tool.
     * Might not be available in some cases, e.g., a build timeout.
     *
     * Generated from protobuf field <code>.google.protobuf.Int32Value build_tool_exit_code = 4;</code>
     */
    private $build_tool_exit_code = null;
    /**
     * Fine-grained diagnostic information to complement the status.
     *
     * Generated from protobuf field <code>.google.protobuf.Any details = 2;</code>
     */
    private $details = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $result
     *           The end result.
     *     @type string $final_invocation_id
     *           Final invocation ID of the build, if there was one.
     *           This field is only set on a status in BuildFinished event.
     *     @type \Google\Protobuf\Int32Value $build_tool_exit_code
     *           Build tool exit code. Integer value returned by the executed build tool.
     *           Might not be available in some cases, e.g., a build timeout.
     *     @type \Google\Protobuf\Any $details
     *           Fine-grained diagnostic information to complement the status.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Build\V1\BuildStatus::initOnce();
        parent::__construct($data);
    }

    /**
     * The end result.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildStatus.Result result = 1;</code>
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * The end result.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildStatus.Result result = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setResult($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildStatus\Result::class);
        $this->result = $var;

        return $this;
    }

    /**
     * Final invocation ID of the build, if there was one.
     * This field is only set on a status in BuildFinished event.
     *
     * Generated from protobuf field <code>string final_invocation_id = 3;</code>
     * @return string
     */
    public function getFinalInvocationId()
    {
        return $this->final_invocation_id;
    }

    /**
     * Final invocation ID of the build, if there was one.
     * This field is only set on a status in BuildFinished event.
     *
     * Generated from protobuf field <code>string final_invocation_id = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setFinalInvocationId($var)
    {
        GPBUtil::checkString($var, True);
        $this->final_invocation_id = $var;

        return $this;
    }

    /**
     * Build tool exit code. Integer value returned by the executed build tool.
     * Might not be available in some cases, e.g., a build timeout.
     *
     * Generated from protobuf field <code>.google.protobuf.Int32Value build_tool_exit_code = 4;</code>
     * @return \Google\Protobuf\Int32Value|null
     */
    public function getBuildToolExitCode()
    {
        return $this->build_tool_exit_code;
    }

    public function hasBuildToolExitCode()
    {
        return isset($this->build_tool_exit_code);
    }

    public function clearBuildToolExitCode()
    {
        unset($this->build_tool_exit_code);
    }

    /**
     * Returns the unboxed value from <code>getBuildToolExitCode()</code>

     * Build tool exit code. Integer value returned by the executed build tool.
     * Might not be available in some cases, e.g., a build timeout.
     *
     * Generated from protobuf field <code>.google.protobuf.Int32Value build_tool_exit_code = 4;</code>
     * @return int|null
     */
    public function getBuildToolExitCodeValue()
    {
        return $this->readWrapperValue("build_tool_exit_code");
    }

    /**
     * Build tool exit code. Integer value returned by the executed build tool.
     * Might not be available in some cases, e.g., a build timeout.
     *
     * Generated from protobuf field <code>.google.protobuf.Int32Value build_tool_exit_code = 4;</code>
     * @param \Google\Protobuf\Int32Value $var
     * @return $this
     */
    public function setBuildToolExitCode($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Int32Value::class);
        $this->build_tool_exit_code = $var;

        return $this;
    }

    /**
     * Sets the field by wrapping a primitive type in a Google\Protobuf\Int32Value object.

     * Build tool exit code. Integer value returned by the executed build tool.
     * Might not be available in some cases, e.g., a build timeout.
     *
     * Generated from protobuf field <code>.google.protobuf.Int32Value build_tool_exit_code = 4;</code>
     * @param int|null $var
     * @return $this
     */
    public function setBuildToolExitCodeValue($var)
    {
        $this->writeWrapperValue("build_tool_exit_code", $var);
        return $this;}

    /**
     * Fine-grained diagnostic information to complement the status.
     *
     * Generated from protobuf field <code>.google.protobuf.Any details = 2;</code>
     * @return \Google\Protobuf\Any|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    public function hasDetails()
    {
        return isset($this->details);
    }

    public function clearDetails()
    {
        unset($this->details);
    }

    /**
     * Fine-grained diagnostic information to complement the status.
     *
     * Generated from protobuf field <code>.google.protobuf.Any details = 2;</code>
     * @param \Google\Protobuf\Any $var
     * @return $this
     */
    public function setDetails($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Any::class);
        $this->details = $var;

        return $this;
    }

}

