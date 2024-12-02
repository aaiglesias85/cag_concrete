<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/optimization/v1/async_model.proto

namespace Google\Cloud\Optimization\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The desired input location information.
 *
 * Generated from protobuf message <code>google.cloud.optimization.v1.InputConfig</code>
 */
class InputConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * The input data format that used to store the model in Cloud Storage.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.DataFormat data_format = 2;</code>
     */
    private $data_format = 0;
    protected $source;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Optimization\V1\GcsSource $gcs_source
     *           The Google Cloud Storage location to read the input from. This must be a
     *           single file.
     *     @type int $data_format
     *           The input data format that used to store the model in Cloud Storage.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Optimization\V1\AsyncModel::initOnce();
        parent::__construct($data);
    }

    /**
     * The Google Cloud Storage location to read the input from. This must be a
     * single file.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.GcsSource gcs_source = 1;</code>
     * @return \Google\Cloud\Optimization\V1\GcsSource|null
     */
    public function getGcsSource()
    {
        return $this->readOneof(1);
    }

    public function hasGcsSource()
    {
        return $this->hasOneof(1);
    }

    /**
     * The Google Cloud Storage location to read the input from. This must be a
     * single file.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.GcsSource gcs_source = 1;</code>
     * @param \Google\Cloud\Optimization\V1\GcsSource $var
     * @return $this
     */
    public function setGcsSource($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Optimization\V1\GcsSource::class);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * The input data format that used to store the model in Cloud Storage.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.DataFormat data_format = 2;</code>
     * @return int
     */
    public function getDataFormat()
    {
        return $this->data_format;
    }

    /**
     * The input data format that used to store the model in Cloud Storage.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.DataFormat data_format = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setDataFormat($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Optimization\V1\DataFormat::class);
        $this->data_format = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->whichOneof("source");
    }

}

