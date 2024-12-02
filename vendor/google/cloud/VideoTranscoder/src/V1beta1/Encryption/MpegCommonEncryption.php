<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/video/transcoder/v1beta1/resources.proto

namespace Google\Cloud\Video\Transcoder\V1beta1\Encryption;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Configuration for MPEG Common Encryption (MPEG-CENC).
 *
 * Generated from protobuf message <code>google.cloud.video.transcoder.v1beta1.Encryption.MpegCommonEncryption</code>
 */
class MpegCommonEncryption extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. 128 bit Key ID represented as lowercase hexadecimal digits for use with
     * common encryption.
     *
     * Generated from protobuf field <code>string key_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $key_id = '';
    /**
     * Required. Specify the encryption scheme.
     * Supported encryption schemes:
     * - 'cenc'
     * - 'cbcs'
     *
     * Generated from protobuf field <code>string scheme = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $scheme = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $key_id
     *           Required. 128 bit Key ID represented as lowercase hexadecimal digits for use with
     *           common encryption.
     *     @type string $scheme
     *           Required. Specify the encryption scheme.
     *           Supported encryption schemes:
     *           - 'cenc'
     *           - 'cbcs'
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Video\Transcoder\V1Beta1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. 128 bit Key ID represented as lowercase hexadecimal digits for use with
     * common encryption.
     *
     * Generated from protobuf field <code>string key_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getKeyId()
    {
        return $this->key_id;
    }

    /**
     * Required. 128 bit Key ID represented as lowercase hexadecimal digits for use with
     * common encryption.
     *
     * Generated from protobuf field <code>string key_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setKeyId($var)
    {
        GPBUtil::checkString($var, True);
        $this->key_id = $var;

        return $this;
    }

    /**
     * Required. Specify the encryption scheme.
     * Supported encryption schemes:
     * - 'cenc'
     * - 'cbcs'
     *
     * Generated from protobuf field <code>string scheme = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Required. Specify the encryption scheme.
     * Supported encryption schemes:
     * - 'cenc'
     * - 'cbcs'
     *
     * Generated from protobuf field <code>string scheme = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setScheme($var)
    {
        GPBUtil::checkString($var, True);
        $this->scheme = $var;

        return $this;
    }

}


