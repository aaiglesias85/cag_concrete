<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/jobs.proto

namespace Google\Cloud\Dataflow\V1beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response to a request to list Cloud Dataflow jobs in a project. This might
 * be a partial response, depending on the page size in the ListJobsRequest.
 * However, if the project does not have any jobs, an instance of
 * ListJobsResponse is not returned and the requests's response
 * body is empty {}.
 *
 * Generated from protobuf message <code>google.dataflow.v1beta3.ListJobsResponse</code>
 */
class ListJobsResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * A subset of the requested job information.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.Job jobs = 1;</code>
     */
    private $jobs;
    /**
     * Set if there may be more results than fit in this response.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';
    /**
     * Zero or more messages describing the [regional endpoints]
     * (https://cloud.google.com/dataflow/docs/concepts/regional-endpoints) that
     * failed to respond.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.FailedLocation failed_location = 3;</code>
     */
    private $failed_location;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dataflow\V1beta3\Job[]|\Google\Protobuf\Internal\RepeatedField $jobs
     *           A subset of the requested job information.
     *     @type string $next_page_token
     *           Set if there may be more results than fit in this response.
     *     @type \Google\Cloud\Dataflow\V1beta3\FailedLocation[]|\Google\Protobuf\Internal\RepeatedField $failed_location
     *           Zero or more messages describing the [regional endpoints]
     *           (https://cloud.google.com/dataflow/docs/concepts/regional-endpoints) that
     *           failed to respond.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Dataflow\V1Beta3\Jobs::initOnce();
        parent::__construct($data);
    }

    /**
     * A subset of the requested job information.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.Job jobs = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * A subset of the requested job information.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.Job jobs = 1;</code>
     * @param \Google\Cloud\Dataflow\V1beta3\Job[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setJobs($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dataflow\V1beta3\Job::class);
        $this->jobs = $arr;

        return $this;
    }

    /**
     * Set if there may be more results than fit in this response.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * Set if there may be more results than fit in this response.
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

    /**
     * Zero or more messages describing the [regional endpoints]
     * (https://cloud.google.com/dataflow/docs/concepts/regional-endpoints) that
     * failed to respond.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.FailedLocation failed_location = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getFailedLocation()
    {
        return $this->failed_location;
    }

    /**
     * Zero or more messages describing the [regional endpoints]
     * (https://cloud.google.com/dataflow/docs/concepts/regional-endpoints) that
     * failed to respond.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.FailedLocation failed_location = 3;</code>
     * @param \Google\Cloud\Dataflow\V1beta3\FailedLocation[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setFailedLocation($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dataflow\V1beta3\FailedLocation::class);
        $this->failed_location = $arr;

        return $this;
    }

}

