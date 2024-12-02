<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/retail/v2/import_config.proto

namespace Google\Cloud\Retail\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata related to the progress of the Import operation. This will be
 * returned by the google.longrunning.Operation.metadata field.
 *
 * Generated from protobuf message <code>google.cloud.retail.v2.ImportMetadata</code>
 */
class ImportMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 1;</code>
     */
    private $create_time = null;
    /**
     * Operation last update time. If the operation is done, this is also the
     * finish time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 2;</code>
     */
    private $update_time = null;
    /**
     * Count of entries that were processed successfully.
     *
     * Generated from protobuf field <code>int64 success_count = 3;</code>
     */
    private $success_count = 0;
    /**
     * Count of entries that encountered errors while processing.
     *
     * Generated from protobuf field <code>int64 failure_count = 4;</code>
     */
    private $failure_count = 0;
    /**
     * Deprecated. This field is never set.
     *
     * Generated from protobuf field <code>string request_id = 5 [deprecated = true];</code>
     * @deprecated
     */
    protected $request_id = '';
    /**
     * Pub/Sub topic for receiving notification. If this field is set,
     * when the import is finished, a notification will be sent to
     * specified Pub/Sub topic. The message data will be JSON string of a
     * [Operation][google.longrunning.Operation].
     * Format of the Pub/Sub topic is `projects/{project}/topics/{topic}`.
     *
     * Generated from protobuf field <code>string notification_pubsub_topic = 6;</code>
     */
    private $notification_pubsub_topic = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Operation create time.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Operation last update time. If the operation is done, this is also the
     *           finish time.
     *     @type int|string $success_count
     *           Count of entries that were processed successfully.
     *     @type int|string $failure_count
     *           Count of entries that encountered errors while processing.
     *     @type string $request_id
     *           Deprecated. This field is never set.
     *     @type string $notification_pubsub_topic
     *           Pub/Sub topic for receiving notification. If this field is set,
     *           when the import is finished, a notification will be sent to
     *           specified Pub/Sub topic. The message data will be JSON string of a
     *           [Operation][google.longrunning.Operation].
     *           Format of the Pub/Sub topic is `projects/{project}/topics/{topic}`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Retail\V2\ImportConfig::initOnce();
        parent::__construct($data);
    }

    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 1;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    public function hasCreateTime()
    {
        return isset($this->create_time);
    }

    public function clearCreateTime()
    {
        unset($this->create_time);
    }

    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 1;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Operation last update time. If the operation is done, this is also the
     * finish time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 2;</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    public function hasUpdateTime()
    {
        return isset($this->update_time);
    }

    public function clearUpdateTime()
    {
        unset($this->update_time);
    }

    /**
     * Operation last update time. If the operation is done, this is also the
     * finish time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 2;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setUpdateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->update_time = $var;

        return $this;
    }

    /**
     * Count of entries that were processed successfully.
     *
     * Generated from protobuf field <code>int64 success_count = 3;</code>
     * @return int|string
     */
    public function getSuccessCount()
    {
        return $this->success_count;
    }

    /**
     * Count of entries that were processed successfully.
     *
     * Generated from protobuf field <code>int64 success_count = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setSuccessCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->success_count = $var;

        return $this;
    }

    /**
     * Count of entries that encountered errors while processing.
     *
     * Generated from protobuf field <code>int64 failure_count = 4;</code>
     * @return int|string
     */
    public function getFailureCount()
    {
        return $this->failure_count;
    }

    /**
     * Count of entries that encountered errors while processing.
     *
     * Generated from protobuf field <code>int64 failure_count = 4;</code>
     * @param int|string $var
     * @return $this
     */
    public function setFailureCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->failure_count = $var;

        return $this;
    }

    /**
     * Deprecated. This field is never set.
     *
     * Generated from protobuf field <code>string request_id = 5 [deprecated = true];</code>
     * @return string
     * @deprecated
     */
    public function getRequestId()
    {
        @trigger_error('request_id is deprecated.', E_USER_DEPRECATED);
        return $this->request_id;
    }

    /**
     * Deprecated. This field is never set.
     *
     * Generated from protobuf field <code>string request_id = 5 [deprecated = true];</code>
     * @param string $var
     * @return $this
     * @deprecated
     */
    public function setRequestId($var)
    {
        @trigger_error('request_id is deprecated.', E_USER_DEPRECATED);
        GPBUtil::checkString($var, True);
        $this->request_id = $var;

        return $this;
    }

    /**
     * Pub/Sub topic for receiving notification. If this field is set,
     * when the import is finished, a notification will be sent to
     * specified Pub/Sub topic. The message data will be JSON string of a
     * [Operation][google.longrunning.Operation].
     * Format of the Pub/Sub topic is `projects/{project}/topics/{topic}`.
     *
     * Generated from protobuf field <code>string notification_pubsub_topic = 6;</code>
     * @return string
     */
    public function getNotificationPubsubTopic()
    {
        return $this->notification_pubsub_topic;
    }

    /**
     * Pub/Sub topic for receiving notification. If this field is set,
     * when the import is finished, a notification will be sent to
     * specified Pub/Sub topic. The message data will be JSON string of a
     * [Operation][google.longrunning.Operation].
     * Format of the Pub/Sub topic is `projects/{project}/topics/{topic}`.
     *
     * Generated from protobuf field <code>string notification_pubsub_topic = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setNotificationPubsubTopic($var)
    {
        GPBUtil::checkString($var, True);
        $this->notification_pubsub_topic = $var;

        return $this;
    }

}

