<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/templates.proto

namespace Google\Cloud\Dataflow\V1beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Parameters to provide to the template being launched.
 *
 * Generated from protobuf message <code>google.dataflow.v1beta3.LaunchTemplateParameters</code>
 */
class LaunchTemplateParameters extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The job name to use for the created job.
     *
     * Generated from protobuf field <code>string job_name = 1;</code>
     */
    private $job_name = '';
    /**
     * The runtime parameters to pass to the job.
     *
     * Generated from protobuf field <code>map<string, string> parameters = 2;</code>
     */
    private $parameters;
    /**
     * The runtime environment for the job.
     *
     * Generated from protobuf field <code>.google.dataflow.v1beta3.RuntimeEnvironment environment = 3;</code>
     */
    private $environment = null;
    /**
     * If set, replace the existing pipeline with the name specified by jobName
     * with this pipeline, preserving state.
     *
     * Generated from protobuf field <code>bool update = 4;</code>
     */
    private $update = false;
    /**
     * Only applicable when updating a pipeline. Map of transform name prefixes of
     * the job to be replaced to the corresponding name prefixes of the new job.
     *
     * Generated from protobuf field <code>map<string, string> transform_name_mapping = 5;</code>
     */
    private $transform_name_mapping;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $job_name
     *           Required. The job name to use for the created job.
     *     @type array|\Google\Protobuf\Internal\MapField $parameters
     *           The runtime parameters to pass to the job.
     *     @type \Google\Cloud\Dataflow\V1beta3\RuntimeEnvironment $environment
     *           The runtime environment for the job.
     *     @type bool $update
     *           If set, replace the existing pipeline with the name specified by jobName
     *           with this pipeline, preserving state.
     *     @type array|\Google\Protobuf\Internal\MapField $transform_name_mapping
     *           Only applicable when updating a pipeline. Map of transform name prefixes of
     *           the job to be replaced to the corresponding name prefixes of the new job.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Dataflow\V1Beta3\Templates::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The job name to use for the created job.
     *
     * Generated from protobuf field <code>string job_name = 1;</code>
     * @return string
     */
    public function getJobName()
    {
        return $this->job_name;
    }

    /**
     * Required. The job name to use for the created job.
     *
     * Generated from protobuf field <code>string job_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setJobName($var)
    {
        GPBUtil::checkString($var, True);
        $this->job_name = $var;

        return $this;
    }

    /**
     * The runtime parameters to pass to the job.
     *
     * Generated from protobuf field <code>map<string, string> parameters = 2;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * The runtime parameters to pass to the job.
     *
     * Generated from protobuf field <code>map<string, string> parameters = 2;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setParameters($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->parameters = $arr;

        return $this;
    }

    /**
     * The runtime environment for the job.
     *
     * Generated from protobuf field <code>.google.dataflow.v1beta3.RuntimeEnvironment environment = 3;</code>
     * @return \Google\Cloud\Dataflow\V1beta3\RuntimeEnvironment|null
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
     * The runtime environment for the job.
     *
     * Generated from protobuf field <code>.google.dataflow.v1beta3.RuntimeEnvironment environment = 3;</code>
     * @param \Google\Cloud\Dataflow\V1beta3\RuntimeEnvironment $var
     * @return $this
     */
    public function setEnvironment($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dataflow\V1beta3\RuntimeEnvironment::class);
        $this->environment = $var;

        return $this;
    }

    /**
     * If set, replace the existing pipeline with the name specified by jobName
     * with this pipeline, preserving state.
     *
     * Generated from protobuf field <code>bool update = 4;</code>
     * @return bool
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * If set, replace the existing pipeline with the name specified by jobName
     * with this pipeline, preserving state.
     *
     * Generated from protobuf field <code>bool update = 4;</code>
     * @param bool $var
     * @return $this
     */
    public function setUpdate($var)
    {
        GPBUtil::checkBool($var);
        $this->update = $var;

        return $this;
    }

    /**
     * Only applicable when updating a pipeline. Map of transform name prefixes of
     * the job to be replaced to the corresponding name prefixes of the new job.
     *
     * Generated from protobuf field <code>map<string, string> transform_name_mapping = 5;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getTransformNameMapping()
    {
        return $this->transform_name_mapping;
    }

    /**
     * Only applicable when updating a pipeline. Map of transform name prefixes of
     * the job to be replaced to the corresponding name prefixes of the new job.
     *
     * Generated from protobuf field <code>map<string, string> transform_name_mapping = 5;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setTransformNameMapping($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->transform_name_mapping = $arr;

        return $this;
    }

}

