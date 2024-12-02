<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/tasks/v2beta2/queue.proto

namespace Google\Cloud\Tasks\V2beta2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Retry config.
 * These settings determine how a failed task attempt is retried.
 *
 * Generated from protobuf message <code>google.cloud.tasks.v2beta2.RetryConfig</code>
 */
class RetryConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * If positive, `max_retry_duration` specifies the time limit for
     * retrying a failed task, measured from when the task was first
     * attempted. Once `max_retry_duration` time has passed *and* the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times, no further attempts will be made and the task will be
     * deleted.
     * If zero, then the task age is unlimited.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_retry_duration` will be truncated to the nearest second.
     * This field has the same meaning as
     * [task_age_limit in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_retry_duration = 3;</code>
     */
    private $max_retry_duration = null;
    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `min_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [min_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration min_backoff = 4;</code>
     */
    private $min_backoff = null;
    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [max_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_backoff = 5;</code>
     */
    private $max_backoff = null;
    /**
     * The time between retries will double `max_doublings` times.
     * A task's retry interval starts at
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff], then doubles
     * `max_doublings` times, then increases linearly, and finally
     * retries at intervals of
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] up to
     * [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts] times.
     * For example, if [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] is 10s,
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] is 300s, and
     * `max_doublings` is 3, then the a task will first be retried in
     * 10s. The retry interval will double three times, and then
     * increase linearly by 2^3 * 10s.  Finally, the task will retry at
     * intervals of [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] until the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times. Thus, the requests will retry at 10s, 20s, 40s, 80s, 160s,
     * 240s, 300s, 300s, ....
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * This field has the same meaning as
     * [max_doublings in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>int32 max_doublings = 6;</code>
     */
    private $max_doublings = 0;
    protected $num_attempts;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $max_attempts
     *           The maximum number of attempts for a task.
     *           Cloud Tasks will attempt the task `max_attempts` times (that
     *           is, if the first attempt fails, then there will be
     *           `max_attempts - 1` retries).  Must be > 0.
     *     @type bool $unlimited_attempts
     *           If true, then the number of attempts is unlimited.
     *     @type \Google\Protobuf\Duration $max_retry_duration
     *           If positive, `max_retry_duration` specifies the time limit for
     *           retrying a failed task, measured from when the task was first
     *           attempted. Once `max_retry_duration` time has passed *and* the
     *           task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     *           times, no further attempts will be made and the task will be
     *           deleted.
     *           If zero, then the task age is unlimited.
     *           If unspecified when the queue is created, Cloud Tasks will pick the
     *           default.
     *           This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     *           `max_retry_duration` will be truncated to the nearest second.
     *           This field has the same meaning as
     *           [task_age_limit in
     *           queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *     @type \Google\Protobuf\Duration $min_backoff
     *           A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     *           [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     *           [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     *           if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     *           retried.
     *           If unspecified when the queue is created, Cloud Tasks will pick the
     *           default.
     *           This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     *           `min_backoff` will be truncated to the nearest second.
     *           This field has the same meaning as
     *           [min_backoff_seconds in
     *           queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *     @type \Google\Protobuf\Duration $max_backoff
     *           A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     *           [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     *           [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     *           if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     *           retried.
     *           If unspecified when the queue is created, Cloud Tasks will pick the
     *           default.
     *           This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     *           `max_backoff` will be truncated to the nearest second.
     *           This field has the same meaning as
     *           [max_backoff_seconds in
     *           queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *     @type int $max_doublings
     *           The time between retries will double `max_doublings` times.
     *           A task's retry interval starts at
     *           [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff], then doubles
     *           `max_doublings` times, then increases linearly, and finally
     *           retries at intervals of
     *           [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] up to
     *           [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts] times.
     *           For example, if [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] is 10s,
     *           [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] is 300s, and
     *           `max_doublings` is 3, then the a task will first be retried in
     *           10s. The retry interval will double three times, and then
     *           increase linearly by 2^3 * 10s.  Finally, the task will retry at
     *           intervals of [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] until the
     *           task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     *           times. Thus, the requests will retry at 10s, 20s, 40s, 80s, 160s,
     *           240s, 300s, 300s, ....
     *           If unspecified when the queue is created, Cloud Tasks will pick the
     *           default.
     *           This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     *           This field has the same meaning as
     *           [max_doublings in
     *           queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Tasks\V2Beta2\Queue::initOnce();
        parent::__construct($data);
    }

    /**
     * The maximum number of attempts for a task.
     * Cloud Tasks will attempt the task `max_attempts` times (that
     * is, if the first attempt fails, then there will be
     * `max_attempts - 1` retries).  Must be > 0.
     *
     * Generated from protobuf field <code>int32 max_attempts = 1;</code>
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->readOneof(1);
    }

    public function hasMaxAttempts()
    {
        return $this->hasOneof(1);
    }

    /**
     * The maximum number of attempts for a task.
     * Cloud Tasks will attempt the task `max_attempts` times (that
     * is, if the first attempt fails, then there will be
     * `max_attempts - 1` retries).  Must be > 0.
     *
     * Generated from protobuf field <code>int32 max_attempts = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setMaxAttempts($var)
    {
        GPBUtil::checkInt32($var);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * If true, then the number of attempts is unlimited.
     *
     * Generated from protobuf field <code>bool unlimited_attempts = 2;</code>
     * @return bool
     */
    public function getUnlimitedAttempts()
    {
        return $this->readOneof(2);
    }

    public function hasUnlimitedAttempts()
    {
        return $this->hasOneof(2);
    }

    /**
     * If true, then the number of attempts is unlimited.
     *
     * Generated from protobuf field <code>bool unlimited_attempts = 2;</code>
     * @param bool $var
     * @return $this
     */
    public function setUnlimitedAttempts($var)
    {
        GPBUtil::checkBool($var);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * If positive, `max_retry_duration` specifies the time limit for
     * retrying a failed task, measured from when the task was first
     * attempted. Once `max_retry_duration` time has passed *and* the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times, no further attempts will be made and the task will be
     * deleted.
     * If zero, then the task age is unlimited.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_retry_duration` will be truncated to the nearest second.
     * This field has the same meaning as
     * [task_age_limit in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_retry_duration = 3;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getMaxRetryDuration()
    {
        return $this->max_retry_duration;
    }

    public function hasMaxRetryDuration()
    {
        return isset($this->max_retry_duration);
    }

    public function clearMaxRetryDuration()
    {
        unset($this->max_retry_duration);
    }

    /**
     * If positive, `max_retry_duration` specifies the time limit for
     * retrying a failed task, measured from when the task was first
     * attempted. Once `max_retry_duration` time has passed *and* the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times, no further attempts will be made and the task will be
     * deleted.
     * If zero, then the task age is unlimited.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_retry_duration` will be truncated to the nearest second.
     * This field has the same meaning as
     * [task_age_limit in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_retry_duration = 3;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setMaxRetryDuration($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->max_retry_duration = $var;

        return $this;
    }

    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `min_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [min_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration min_backoff = 4;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getMinBackoff()
    {
        return $this->min_backoff;
    }

    public function hasMinBackoff()
    {
        return isset($this->min_backoff);
    }

    public function clearMinBackoff()
    {
        unset($this->min_backoff);
    }

    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `min_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [min_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration min_backoff = 4;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setMinBackoff($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->min_backoff = $var;

        return $this;
    }

    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [max_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_backoff = 5;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getMaxBackoff()
    {
        return $this->max_backoff;
    }

    public function hasMaxBackoff()
    {
        return isset($this->max_backoff);
    }

    public function clearMaxBackoff()
    {
        unset($this->max_backoff);
    }

    /**
     * A task will be [scheduled][google.cloud.tasks.v2beta2.Task.schedule_time] for retry between
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] and
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] duration after it fails,
     * if the queue's [RetryConfig][google.cloud.tasks.v2beta2.RetryConfig] specifies that the task should be
     * retried.
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * `max_backoff` will be truncated to the nearest second.
     * This field has the same meaning as
     * [max_backoff_seconds in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration max_backoff = 5;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setMaxBackoff($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->max_backoff = $var;

        return $this;
    }

    /**
     * The time between retries will double `max_doublings` times.
     * A task's retry interval starts at
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff], then doubles
     * `max_doublings` times, then increases linearly, and finally
     * retries at intervals of
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] up to
     * [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts] times.
     * For example, if [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] is 10s,
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] is 300s, and
     * `max_doublings` is 3, then the a task will first be retried in
     * 10s. The retry interval will double three times, and then
     * increase linearly by 2^3 * 10s.  Finally, the task will retry at
     * intervals of [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] until the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times. Thus, the requests will retry at 10s, 20s, 40s, 80s, 160s,
     * 240s, 300s, 300s, ....
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * This field has the same meaning as
     * [max_doublings in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>int32 max_doublings = 6;</code>
     * @return int
     */
    public function getMaxDoublings()
    {
        return $this->max_doublings;
    }

    /**
     * The time between retries will double `max_doublings` times.
     * A task's retry interval starts at
     * [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff], then doubles
     * `max_doublings` times, then increases linearly, and finally
     * retries at intervals of
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] up to
     * [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts] times.
     * For example, if [min_backoff][google.cloud.tasks.v2beta2.RetryConfig.min_backoff] is 10s,
     * [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] is 300s, and
     * `max_doublings` is 3, then the a task will first be retried in
     * 10s. The retry interval will double three times, and then
     * increase linearly by 2^3 * 10s.  Finally, the task will retry at
     * intervals of [max_backoff][google.cloud.tasks.v2beta2.RetryConfig.max_backoff] until the
     * task has been attempted [max_attempts][google.cloud.tasks.v2beta2.RetryConfig.max_attempts]
     * times. Thus, the requests will retry at 10s, 20s, 40s, 80s, 160s,
     * 240s, 300s, 300s, ....
     * If unspecified when the queue is created, Cloud Tasks will pick the
     * default.
     * This field is output only for [pull queues][google.cloud.tasks.v2beta2.PullTarget].
     * This field has the same meaning as
     * [max_doublings in
     * queue.yaml/xml](https://cloud.google.com/appengine/docs/standard/python/config/queueref#retry_parameters).
     *
     * Generated from protobuf field <code>int32 max_doublings = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setMaxDoublings($var)
    {
        GPBUtil::checkInt32($var);
        $this->max_doublings = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumAttempts()
    {
        return $this->whichOneof("num_attempts");
    }

}

