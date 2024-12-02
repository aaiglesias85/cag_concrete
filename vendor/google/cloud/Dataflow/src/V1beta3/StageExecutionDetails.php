<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/metrics.proto

namespace Google\Cloud\Dataflow\V1beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Information about the workers and work items within a stage.
 *
 * Generated from protobuf message <code>google.dataflow.v1beta3.StageExecutionDetails</code>
 */
class StageExecutionDetails extends \Google\Protobuf\Internal\Message
{
    /**
     * Workers that have done work on the stage.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.WorkerDetails workers = 1;</code>
     */
    private $workers;
    /**
     * If present, this response does not contain all requested tasks.  To obtain
     * the next page of results, repeat the request with page_token set to this
     * value.
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
     *     @type \Google\Cloud\Dataflow\V1beta3\WorkerDetails[]|\Google\Protobuf\Internal\RepeatedField $workers
     *           Workers that have done work on the stage.
     *     @type string $next_page_token
     *           If present, this response does not contain all requested tasks.  To obtain
     *           the next page of results, repeat the request with page_token set to this
     *           value.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Dataflow\V1Beta3\Metrics::initOnce();
        parent::__construct($data);
    }

    /**
     * Workers that have done work on the stage.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.WorkerDetails workers = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getWorkers()
    {
        return $this->workers;
    }

    /**
     * Workers that have done work on the stage.
     *
     * Generated from protobuf field <code>repeated .google.dataflow.v1beta3.WorkerDetails workers = 1;</code>
     * @param \Google\Cloud\Dataflow\V1beta3\WorkerDetails[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setWorkers($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dataflow\V1beta3\WorkerDetails::class);
        $this->workers = $arr;

        return $this;
    }

    /**
     * If present, this response does not contain all requested tasks.  To obtain
     * the next page of results, repeat the request with page_token set to this
     * value.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * If present, this response does not contain all requested tasks.  To obtain
     * the next page of results, repeat the request with page_token set to this
     * value.
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

