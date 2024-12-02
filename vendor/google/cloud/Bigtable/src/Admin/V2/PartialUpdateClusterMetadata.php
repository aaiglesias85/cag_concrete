<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/bigtable/admin/v2/bigtable_instance_admin.proto

namespace Google\Cloud\Bigtable\Admin\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The metadata for the Operation returned by PartialUpdateCluster.
 *
 * Generated from protobuf message <code>google.bigtable.admin.v2.PartialUpdateClusterMetadata</code>
 */
class PartialUpdateClusterMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * The time at which the original request was received.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp request_time = 1;</code>
     */
    private $request_time = null;
    /**
     * The time at which the operation failed or was completed successfully.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp finish_time = 2;</code>
     */
    private $finish_time = null;
    /**
     * The original request for PartialUpdateCluster.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.PartialUpdateClusterRequest original_request = 3;</code>
     */
    private $original_request = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Protobuf\Timestamp $request_time
     *           The time at which the original request was received.
     *     @type \Google\Protobuf\Timestamp $finish_time
     *           The time at which the operation failed or was completed successfully.
     *     @type \Google\Cloud\Bigtable\Admin\V2\PartialUpdateClusterRequest $original_request
     *           The original request for PartialUpdateCluster.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Bigtable\Admin\V2\BigtableInstanceAdmin::initOnce();
        parent::__construct($data);
    }

    /**
     * The time at which the original request was received.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp request_time = 1;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getRequestTime()
    {
        return $this->request_time;
    }

    public function hasRequestTime()
    {
        return isset($this->request_time);
    }

    public function clearRequestTime()
    {
        unset($this->request_time);
    }

    /**
     * The time at which the original request was received.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp request_time = 1;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setRequestTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->request_time = $var;

        return $this;
    }

    /**
     * The time at which the operation failed or was completed successfully.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp finish_time = 2;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getFinishTime()
    {
        return $this->finish_time;
    }

    public function hasFinishTime()
    {
        return isset($this->finish_time);
    }

    public function clearFinishTime()
    {
        unset($this->finish_time);
    }

    /**
     * The time at which the operation failed or was completed successfully.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp finish_time = 2;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setFinishTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->finish_time = $var;

        return $this;
    }

    /**
     * The original request for PartialUpdateCluster.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.PartialUpdateClusterRequest original_request = 3;</code>
     * @return \Google\Cloud\Bigtable\Admin\V2\PartialUpdateClusterRequest|null
     */
    public function getOriginalRequest()
    {
        return $this->original_request;
    }

    public function hasOriginalRequest()
    {
        return isset($this->original_request);
    }

    public function clearOriginalRequest()
    {
        unset($this->original_request);
    }

    /**
     * The original request for PartialUpdateCluster.
     *
     * Generated from protobuf field <code>.google.bigtable.admin.v2.PartialUpdateClusterRequest original_request = 3;</code>
     * @param \Google\Cloud\Bigtable\Admin\V2\PartialUpdateClusterRequest $var
     * @return $this
     */
    public function setOriginalRequest($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Bigtable\Admin\V2\PartialUpdateClusterRequest::class);
        $this->original_request = $var;

        return $this;
    }

}

