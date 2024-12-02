<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datastream/v1/datastream_resources.proto

namespace Google\Cloud\Datastream\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A specific stream object (e.g a specific DB table).
 *
 * Generated from protobuf message <code>google.cloud.datastream.v1.StreamObject</code>
 */
class StreamObject extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. The object resource's name.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Output only. The creation time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. The last update time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $update_time = null;
    /**
     * Required. Display name.
     *
     * Generated from protobuf field <code>string display_name = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $display_name = '';
    /**
     * Output only. Active errors on the object.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.Error errors = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $errors;
    /**
     * The latest backfill job that was initiated for the stream object.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.BackfillJob backfill_job = 7;</code>
     */
    private $backfill_job = null;
    /**
     * The object identifier in the data source.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.SourceObjectIdentifier source_object = 8;</code>
     */
    private $source_object = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. The object resource's name.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. The creation time of the object.
     *     @type \Google\Protobuf\Timestamp $update_time
     *           Output only. The last update time of the object.
     *     @type string $display_name
     *           Required. Display name.
     *     @type \Google\Cloud\Datastream\V1\Error[]|\Google\Protobuf\Internal\RepeatedField $errors
     *           Output only. Active errors on the object.
     *     @type \Google\Cloud\Datastream\V1\BackfillJob $backfill_job
     *           The latest backfill job that was initiated for the stream object.
     *     @type \Google\Cloud\Datastream\V1\SourceObjectIdentifier $source_object
     *           The object identifier in the data source.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datastream\V1\DatastreamResources::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. The object resource's name.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. The object resource's name.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Output only. The creation time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. The creation time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. The last update time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Output only. The last update time of the object.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp update_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
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
     * Required. Display name.
     *
     * Generated from protobuf field <code>string display_name = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Required. Display name.
     *
     * Generated from protobuf field <code>string display_name = 5 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * Output only. Active errors on the object.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.Error errors = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Output only. Active errors on the object.
     *
     * Generated from protobuf field <code>repeated .google.cloud.datastream.v1.Error errors = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Datastream\V1\Error[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setErrors($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Datastream\V1\Error::class);
        $this->errors = $arr;

        return $this;
    }

    /**
     * The latest backfill job that was initiated for the stream object.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.BackfillJob backfill_job = 7;</code>
     * @return \Google\Cloud\Datastream\V1\BackfillJob|null
     */
    public function getBackfillJob()
    {
        return $this->backfill_job;
    }

    public function hasBackfillJob()
    {
        return isset($this->backfill_job);
    }

    public function clearBackfillJob()
    {
        unset($this->backfill_job);
    }

    /**
     * The latest backfill job that was initiated for the stream object.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.BackfillJob backfill_job = 7;</code>
     * @param \Google\Cloud\Datastream\V1\BackfillJob $var
     * @return $this
     */
    public function setBackfillJob($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Datastream\V1\BackfillJob::class);
        $this->backfill_job = $var;

        return $this;
    }

    /**
     * The object identifier in the data source.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.SourceObjectIdentifier source_object = 8;</code>
     * @return \Google\Cloud\Datastream\V1\SourceObjectIdentifier|null
     */
    public function getSourceObject()
    {
        return $this->source_object;
    }

    public function hasSourceObject()
    {
        return isset($this->source_object);
    }

    public function clearSourceObject()
    {
        unset($this->source_object);
    }

    /**
     * The object identifier in the data source.
     *
     * Generated from protobuf field <code>.google.cloud.datastream.v1.SourceObjectIdentifier source_object = 8;</code>
     * @param \Google\Cloud\Datastream\V1\SourceObjectIdentifier $var
     * @return $this
     */
    public function setSourceObject($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Datastream\V1\SourceObjectIdentifier::class);
        $this->source_object = $var;

        return $this;
    }

}

