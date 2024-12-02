<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/jobs.proto

namespace Google\Cloud\Dataflow\V1beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response for CheckActiveJobsRequest.
 *
 * Generated from protobuf message <code>google.dataflow.v1beta3.CheckActiveJobsResponse</code>
 */
class CheckActiveJobsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * If True, active jobs exists for project. False otherwise.
     *
     * Generated from protobuf field <code>bool active_jobs_exist = 1;</code>
     */
    private $active_jobs_exist = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $active_jobs_exist
     *           If True, active jobs exists for project. False otherwise.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Dataflow\V1Beta3\Jobs::initOnce();
        parent::__construct($data);
    }

    /**
     * If True, active jobs exists for project. False otherwise.
     *
     * Generated from protobuf field <code>bool active_jobs_exist = 1;</code>
     * @return bool
     */
    public function getActiveJobsExist()
    {
        return $this->active_jobs_exist;
    }

    /**
     * If True, active jobs exists for project. False otherwise.
     *
     * Generated from protobuf field <code>bool active_jobs_exist = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setActiveJobsExist($var)
    {
        GPBUtil::checkBool($var);
        $this->active_jobs_exist = $var;

        return $this;
    }

}

