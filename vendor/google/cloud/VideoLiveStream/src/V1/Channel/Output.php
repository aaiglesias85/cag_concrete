<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/video/livestream/v1/resources.proto

namespace Google\Cloud\Video\LiveStream\V1\Channel;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Location of output file(s) in a Google Cloud Storage bucket.
 *
 * Generated from protobuf message <code>google.cloud.video.livestream.v1.Channel.Output</code>
 */
class Output extends \Google\Protobuf\Internal\Message
{
    /**
     * URI for the output file(s). For example, `gs://my-bucket/outputs/`.
     *
     * Generated from protobuf field <code>string uri = 1;</code>
     */
    private $uri = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $uri
     *           URI for the output file(s). For example, `gs://my-bucket/outputs/`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Video\Livestream\V1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * URI for the output file(s). For example, `gs://my-bucket/outputs/`.
     *
     * Generated from protobuf field <code>string uri = 1;</code>
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * URI for the output file(s). For example, `gs://my-bucket/outputs/`.
     *
     * Generated from protobuf field <code>string uri = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->uri = $var;

        return $this;
    }

}


