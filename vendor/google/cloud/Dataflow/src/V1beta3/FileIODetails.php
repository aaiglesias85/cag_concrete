<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/dataflow/v1beta3/jobs.proto

namespace Google\Cloud\Dataflow\V1beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata for a File connector used by the job.
 *
 * Generated from protobuf message <code>google.dataflow.v1beta3.FileIODetails</code>
 */
class FileIODetails extends \Google\Protobuf\Internal\Message
{
    /**
     * File Pattern used to access files by the connector.
     *
     * Generated from protobuf field <code>string file_pattern = 1;</code>
     */
    private $file_pattern = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $file_pattern
     *           File Pattern used to access files by the connector.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Dataflow\V1Beta3\Jobs::initOnce();
        parent::__construct($data);
    }

    /**
     * File Pattern used to access files by the connector.
     *
     * Generated from protobuf field <code>string file_pattern = 1;</code>
     * @return string
     */
    public function getFilePattern()
    {
        return $this->file_pattern;
    }

    /**
     * File Pattern used to access files by the connector.
     *
     * Generated from protobuf field <code>string file_pattern = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setFilePattern($var)
    {
        GPBUtil::checkString($var, True);
        $this->file_pattern = $var;

        return $this;
    }

}

