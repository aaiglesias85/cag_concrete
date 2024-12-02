<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/datalabeling/v1beta1/data_payloads.proto

namespace Google\Cloud\DataLabeling\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Container of information of a video thumbnail.
 *
 * Generated from protobuf message <code>google.cloud.datalabeling.v1beta1.VideoThumbnail</code>
 */
class VideoThumbnail extends \Google\Protobuf\Internal\Message
{
    /**
     * A byte string of the video frame.
     *
     * Generated from protobuf field <code>bytes thumbnail = 1;</code>
     */
    private $thumbnail = '';
    /**
     * Time offset relative to the beginning of the video, corresponding to the
     * video frame where the thumbnail has been extracted from.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration time_offset = 2;</code>
     */
    private $time_offset = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $thumbnail
     *           A byte string of the video frame.
     *     @type \Google\Protobuf\Duration $time_offset
     *           Time offset relative to the beginning of the video, corresponding to the
     *           video frame where the thumbnail has been extracted from.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Datalabeling\V1Beta1\DataPayloads::initOnce();
        parent::__construct($data);
    }

    /**
     * A byte string of the video frame.
     *
     * Generated from protobuf field <code>bytes thumbnail = 1;</code>
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * A byte string of the video frame.
     *
     * Generated from protobuf field <code>bytes thumbnail = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setThumbnail($var)
    {
        GPBUtil::checkString($var, False);
        $this->thumbnail = $var;

        return $this;
    }

    /**
     * Time offset relative to the beginning of the video, corresponding to the
     * video frame where the thumbnail has been extracted from.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration time_offset = 2;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getTimeOffset()
    {
        return $this->time_offset;
    }

    public function hasTimeOffset()
    {
        return isset($this->time_offset);
    }

    public function clearTimeOffset()
    {
        unset($this->time_offset);
    }

    /**
     * Time offset relative to the beginning of the video, corresponding to the
     * video frame where the thumbnail has been extracted from.
     *
     * Generated from protobuf field <code>.google.protobuf.Duration time_offset = 2;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setTimeOffset($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->time_offset = $var;

        return $this;
    }

}

