<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/texttospeech/v1/cloud_tts.proto

namespace Google\Cloud\TextToSpeech\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Contains text input to be synthesized. Either `text` or `ssml` must be
 * supplied. Supplying both or neither returns
 * [google.rpc.Code.INVALID_ARGUMENT][]. The input size is limited to 5000
 * characters.
 *
 * Generated from protobuf message <code>google.cloud.texttospeech.v1.SynthesisInput</code>
 */
class SynthesisInput extends \Google\Protobuf\Internal\Message
{
    protected $input_source;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $text
     *           The raw text to be synthesized.
     *     @type string $ssml
     *           The SSML document to be synthesized. The SSML document must be valid
     *           and well-formed. Otherwise the RPC will fail and return
     *           [google.rpc.Code.INVALID_ARGUMENT][]. For more information, see
     *           [SSML](https://cloud.google.com/text-to-speech/docs/ssml).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Texttospeech\V1\CloudTts::initOnce();
        parent::__construct($data);
    }

    /**
     * The raw text to be synthesized.
     *
     * Generated from protobuf field <code>string text = 1;</code>
     * @return string
     */
    public function getText()
    {
        return $this->readOneof(1);
    }

    public function hasText()
    {
        return $this->hasOneof(1);
    }

    /**
     * The raw text to be synthesized.
     *
     * Generated from protobuf field <code>string text = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setText($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * The SSML document to be synthesized. The SSML document must be valid
     * and well-formed. Otherwise the RPC will fail and return
     * [google.rpc.Code.INVALID_ARGUMENT][]. For more information, see
     * [SSML](https://cloud.google.com/text-to-speech/docs/ssml).
     *
     * Generated from protobuf field <code>string ssml = 2;</code>
     * @return string
     */
    public function getSsml()
    {
        return $this->readOneof(2);
    }

    public function hasSsml()
    {
        return $this->hasOneof(2);
    }

    /**
     * The SSML document to be synthesized. The SSML document must be valid
     * and well-formed. Otherwise the RPC will fail and return
     * [google.rpc.Code.INVALID_ARGUMENT][]. For more information, see
     * [SSML](https://cloud.google.com/text-to-speech/docs/ssml).
     *
     * Generated from protobuf field <code>string ssml = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setSsml($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputSource()
    {
        return $this->whichOneof("input_source");
    }

}

