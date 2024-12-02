<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/cloudbuild/v1/cloudbuild.proto

namespace Google\Cloud\Build\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Optional arguments to enable specific features of builds.
 *
 * Generated from protobuf message <code>google.devtools.cloudbuild.v1.BuildOptions</code>
 */
class BuildOptions extends \Google\Protobuf\Internal\Message
{
    /**
     * Requested hash for SourceProvenance.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Hash.HashType source_provenance_hash = 1;</code>
     */
    private $source_provenance_hash;
    /**
     * Requested verifiability options.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.VerifyOption requested_verify_option = 2;</code>
     */
    private $requested_verify_option = 0;
    /**
     * Compute Engine machine type on which to run the build.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.MachineType machine_type = 3;</code>
     */
    private $machine_type = 0;
    /**
     * Requested disk size for the VM that runs the build. Note that this is *NOT*
     * "disk free"; some of the space will be used by the operating system and
     * build utilities. Also note that this is the minimum disk size that will be
     * allocated for the build -- the build may run with a larger disk than
     * requested. At present, the maximum disk size is 1000GB; builds that request
     * more than the maximum are rejected with an error.
     *
     * Generated from protobuf field <code>int64 disk_size_gb = 6;</code>
     */
    private $disk_size_gb = 0;
    /**
     * Option to specify behavior when there is an error in the substitution
     * checks.
     * NOTE: this is always set to ALLOW_LOOSE for triggered builds and cannot
     * be overridden in the build configuration file.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.SubstitutionOption substitution_option = 4;</code>
     */
    private $substitution_option = 0;
    /**
     * Option to specify whether or not to apply bash style string
     * operations to the substitutions.
     * NOTE: this is always enabled for triggered builds and cannot be
     * overridden in the build configuration file.
     *
     * Generated from protobuf field <code>bool dynamic_substitutions = 17;</code>
     */
    private $dynamic_substitutions = false;
    /**
     * Option to define build log streaming behavior to Google Cloud
     * Storage.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LogStreamingOption log_streaming_option = 5;</code>
     */
    private $log_streaming_option = 0;
    /**
     * Option to specify a `WorkerPool` for the build.
     * Format: projects/{project}/locations/{location}/workerPools/{workerPool}
     * This field is experimental.
     *
     * Generated from protobuf field <code>string worker_pool = 7;</code>
     */
    private $worker_pool = '';
    /**
     * Option to specify the logging mode, which determines if and where build
     * logs are stored.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LoggingMode logging = 11;</code>
     */
    private $logging = 0;
    /**
     * A list of global environment variable definitions that will exist for all
     * build steps in this build. If a variable is defined in both globally and in
     * a build step, the variable will use the build step value.
     * The elements are of the form "KEY=VALUE" for the environment variable "KEY"
     * being given the value "VALUE".
     *
     * Generated from protobuf field <code>repeated string env = 12;</code>
     */
    private $env;
    /**
     * A list of global environment variables, which are encrypted using a Cloud
     * Key Management Service crypto key. These values must be specified in the
     * build's `Secret`. These variables will be available to all build steps
     * in this build.
     *
     * Generated from protobuf field <code>repeated string secret_env = 13;</code>
     */
    private $secret_env;
    /**
     * Global list of volumes to mount for ALL build steps
     * Each volume is created as an empty volume prior to starting the build
     * process. Upon completion of the build, volumes and their contents are
     * discarded. Global volume names and paths cannot conflict with the volumes
     * defined a build step.
     * Using a global volume in a build with only one step is not valid as
     * it is indicative of a build request with an incorrect configuration.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Volume volumes = 14;</code>
     */
    private $volumes;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $source_provenance_hash
     *           Requested hash for SourceProvenance.
     *     @type int $requested_verify_option
     *           Requested verifiability options.
     *     @type int $machine_type
     *           Compute Engine machine type on which to run the build.
     *     @type int|string $disk_size_gb
     *           Requested disk size for the VM that runs the build. Note that this is *NOT*
     *           "disk free"; some of the space will be used by the operating system and
     *           build utilities. Also note that this is the minimum disk size that will be
     *           allocated for the build -- the build may run with a larger disk than
     *           requested. At present, the maximum disk size is 1000GB; builds that request
     *           more than the maximum are rejected with an error.
     *     @type int $substitution_option
     *           Option to specify behavior when there is an error in the substitution
     *           checks.
     *           NOTE: this is always set to ALLOW_LOOSE for triggered builds and cannot
     *           be overridden in the build configuration file.
     *     @type bool $dynamic_substitutions
     *           Option to specify whether or not to apply bash style string
     *           operations to the substitutions.
     *           NOTE: this is always enabled for triggered builds and cannot be
     *           overridden in the build configuration file.
     *     @type int $log_streaming_option
     *           Option to define build log streaming behavior to Google Cloud
     *           Storage.
     *     @type string $worker_pool
     *           Option to specify a `WorkerPool` for the build.
     *           Format: projects/{project}/locations/{location}/workerPools/{workerPool}
     *           This field is experimental.
     *     @type int $logging
     *           Option to specify the logging mode, which determines if and where build
     *           logs are stored.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $env
     *           A list of global environment variable definitions that will exist for all
     *           build steps in this build. If a variable is defined in both globally and in
     *           a build step, the variable will use the build step value.
     *           The elements are of the form "KEY=VALUE" for the environment variable "KEY"
     *           being given the value "VALUE".
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $secret_env
     *           A list of global environment variables, which are encrypted using a Cloud
     *           Key Management Service crypto key. These values must be specified in the
     *           build's `Secret`. These variables will be available to all build steps
     *           in this build.
     *     @type \Google\Cloud\Build\V1\Volume[]|\Google\Protobuf\Internal\RepeatedField $volumes
     *           Global list of volumes to mount for ALL build steps
     *           Each volume is created as an empty volume prior to starting the build
     *           process. Upon completion of the build, volumes and their contents are
     *           discarded. Global volume names and paths cannot conflict with the volumes
     *           defined a build step.
     *           Using a global volume in a build with only one step is not valid as
     *           it is indicative of a build request with an incorrect configuration.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Cloudbuild\V1\Cloudbuild::initOnce();
        parent::__construct($data);
    }

    /**
     * Requested hash for SourceProvenance.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Hash.HashType source_provenance_hash = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSourceProvenanceHash()
    {
        return $this->source_provenance_hash;
    }

    /**
     * Requested hash for SourceProvenance.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Hash.HashType source_provenance_hash = 1;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSourceProvenanceHash($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::ENUM, \Google\Cloud\Build\V1\Hash\HashType::class);
        $this->source_provenance_hash = $arr;

        return $this;
    }

    /**
     * Requested verifiability options.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.VerifyOption requested_verify_option = 2;</code>
     * @return int
     */
    public function getRequestedVerifyOption()
    {
        return $this->requested_verify_option;
    }

    /**
     * Requested verifiability options.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.VerifyOption requested_verify_option = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setRequestedVerifyOption($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildOptions\VerifyOption::class);
        $this->requested_verify_option = $var;

        return $this;
    }

    /**
     * Compute Engine machine type on which to run the build.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.MachineType machine_type = 3;</code>
     * @return int
     */
    public function getMachineType()
    {
        return $this->machine_type;
    }

    /**
     * Compute Engine machine type on which to run the build.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.MachineType machine_type = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setMachineType($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildOptions\MachineType::class);
        $this->machine_type = $var;

        return $this;
    }

    /**
     * Requested disk size for the VM that runs the build. Note that this is *NOT*
     * "disk free"; some of the space will be used by the operating system and
     * build utilities. Also note that this is the minimum disk size that will be
     * allocated for the build -- the build may run with a larger disk than
     * requested. At present, the maximum disk size is 1000GB; builds that request
     * more than the maximum are rejected with an error.
     *
     * Generated from protobuf field <code>int64 disk_size_gb = 6;</code>
     * @return int|string
     */
    public function getDiskSizeGb()
    {
        return $this->disk_size_gb;
    }

    /**
     * Requested disk size for the VM that runs the build. Note that this is *NOT*
     * "disk free"; some of the space will be used by the operating system and
     * build utilities. Also note that this is the minimum disk size that will be
     * allocated for the build -- the build may run with a larger disk than
     * requested. At present, the maximum disk size is 1000GB; builds that request
     * more than the maximum are rejected with an error.
     *
     * Generated from protobuf field <code>int64 disk_size_gb = 6;</code>
     * @param int|string $var
     * @return $this
     */
    public function setDiskSizeGb($var)
    {
        GPBUtil::checkInt64($var);
        $this->disk_size_gb = $var;

        return $this;
    }

    /**
     * Option to specify behavior when there is an error in the substitution
     * checks.
     * NOTE: this is always set to ALLOW_LOOSE for triggered builds and cannot
     * be overridden in the build configuration file.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.SubstitutionOption substitution_option = 4;</code>
     * @return int
     */
    public function getSubstitutionOption()
    {
        return $this->substitution_option;
    }

    /**
     * Option to specify behavior when there is an error in the substitution
     * checks.
     * NOTE: this is always set to ALLOW_LOOSE for triggered builds and cannot
     * be overridden in the build configuration file.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.SubstitutionOption substitution_option = 4;</code>
     * @param int $var
     * @return $this
     */
    public function setSubstitutionOption($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildOptions\SubstitutionOption::class);
        $this->substitution_option = $var;

        return $this;
    }

    /**
     * Option to specify whether or not to apply bash style string
     * operations to the substitutions.
     * NOTE: this is always enabled for triggered builds and cannot be
     * overridden in the build configuration file.
     *
     * Generated from protobuf field <code>bool dynamic_substitutions = 17;</code>
     * @return bool
     */
    public function getDynamicSubstitutions()
    {
        return $this->dynamic_substitutions;
    }

    /**
     * Option to specify whether or not to apply bash style string
     * operations to the substitutions.
     * NOTE: this is always enabled for triggered builds and cannot be
     * overridden in the build configuration file.
     *
     * Generated from protobuf field <code>bool dynamic_substitutions = 17;</code>
     * @param bool $var
     * @return $this
     */
    public function setDynamicSubstitutions($var)
    {
        GPBUtil::checkBool($var);
        $this->dynamic_substitutions = $var;

        return $this;
    }

    /**
     * Option to define build log streaming behavior to Google Cloud
     * Storage.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LogStreamingOption log_streaming_option = 5;</code>
     * @return int
     */
    public function getLogStreamingOption()
    {
        return $this->log_streaming_option;
    }

    /**
     * Option to define build log streaming behavior to Google Cloud
     * Storage.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LogStreamingOption log_streaming_option = 5;</code>
     * @param int $var
     * @return $this
     */
    public function setLogStreamingOption($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildOptions\LogStreamingOption::class);
        $this->log_streaming_option = $var;

        return $this;
    }

    /**
     * Option to specify a `WorkerPool` for the build.
     * Format: projects/{project}/locations/{location}/workerPools/{workerPool}
     * This field is experimental.
     *
     * Generated from protobuf field <code>string worker_pool = 7;</code>
     * @return string
     */
    public function getWorkerPool()
    {
        return $this->worker_pool;
    }

    /**
     * Option to specify a `WorkerPool` for the build.
     * Format: projects/{project}/locations/{location}/workerPools/{workerPool}
     * This field is experimental.
     *
     * Generated from protobuf field <code>string worker_pool = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setWorkerPool($var)
    {
        GPBUtil::checkString($var, True);
        $this->worker_pool = $var;

        return $this;
    }

    /**
     * Option to specify the logging mode, which determines if and where build
     * logs are stored.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LoggingMode logging = 11;</code>
     * @return int
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * Option to specify the logging mode, which determines if and where build
     * logs are stored.
     *
     * Generated from protobuf field <code>.google.devtools.cloudbuild.v1.BuildOptions.LoggingMode logging = 11;</code>
     * @param int $var
     * @return $this
     */
    public function setLogging($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildOptions\LoggingMode::class);
        $this->logging = $var;

        return $this;
    }

    /**
     * A list of global environment variable definitions that will exist for all
     * build steps in this build. If a variable is defined in both globally and in
     * a build step, the variable will use the build step value.
     * The elements are of the form "KEY=VALUE" for the environment variable "KEY"
     * being given the value "VALUE".
     *
     * Generated from protobuf field <code>repeated string env = 12;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * A list of global environment variable definitions that will exist for all
     * build steps in this build. If a variable is defined in both globally and in
     * a build step, the variable will use the build step value.
     * The elements are of the form "KEY=VALUE" for the environment variable "KEY"
     * being given the value "VALUE".
     *
     * Generated from protobuf field <code>repeated string env = 12;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setEnv($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->env = $arr;

        return $this;
    }

    /**
     * A list of global environment variables, which are encrypted using a Cloud
     * Key Management Service crypto key. These values must be specified in the
     * build's `Secret`. These variables will be available to all build steps
     * in this build.
     *
     * Generated from protobuf field <code>repeated string secret_env = 13;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSecretEnv()
    {
        return $this->secret_env;
    }

    /**
     * A list of global environment variables, which are encrypted using a Cloud
     * Key Management Service crypto key. These values must be specified in the
     * build's `Secret`. These variables will be available to all build steps
     * in this build.
     *
     * Generated from protobuf field <code>repeated string secret_env = 13;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSecretEnv($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->secret_env = $arr;

        return $this;
    }

    /**
     * Global list of volumes to mount for ALL build steps
     * Each volume is created as an empty volume prior to starting the build
     * process. Upon completion of the build, volumes and their contents are
     * discarded. Global volume names and paths cannot conflict with the volumes
     * defined a build step.
     * Using a global volume in a build with only one step is not valid as
     * it is indicative of a build request with an incorrect configuration.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Volume volumes = 14;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getVolumes()
    {
        return $this->volumes;
    }

    /**
     * Global list of volumes to mount for ALL build steps
     * Each volume is created as an empty volume prior to starting the build
     * process. Upon completion of the build, volumes and their contents are
     * discarded. Global volume names and paths cannot conflict with the volumes
     * defined a build step.
     * Using a global volume in a build with only one step is not valid as
     * it is indicative of a build request with an incorrect configuration.
     *
     * Generated from protobuf field <code>repeated .google.devtools.cloudbuild.v1.Volume volumes = 14;</code>
     * @param \Google\Cloud\Build\V1\Volume[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setVolumes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Build\V1\Volume::class);
        $this->volumes = $arr;

        return $this;
    }

}

