<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/texttospeech/v1/cloud_tts.proto

namespace Google\Cloud\TextToSpeech\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The message returned to the client by the `SynthesizeSpeech` method.
 *
 * Generated from protobuf message <code>google.cloud.texttospeech.v1.SynthesizeSpeechResponse</code>
 */
class SynthesizeSpeechResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The audio data bytes encoded as specified in the request, including the
     * header for encodings that are wrapped in containers (e.g. MP3, OGG_OPUS).
     * For LINEAR16 audio, we include the WAV header. Note: as
     * with all bytes fields, protobuffers use a pure binary representation,
     * whereas JSON representations use base64.
     *
     * Generated from protobuf field <code>bytes audio_content = 1;</code>
     */
    private $audio_content = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $audio_content
     *           The audio data bytes encoded as specified in the request, including the
     *           header for encodings that are wrapped in containers (e.g. MP3, OGG_OPUS).
     *           For LINEAR16 audio, we include the WAV header. Note: as
     *           with all bytes fields, protobuffers use a pure binary representation,
     *           whereas JSON representations use base64.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Texttospeech\V1\CloudTts::initOnce();
        parent::__construct($data);
    }

    /**
     * The audio data bytes encoded as specified in the request, including the
     * header for encodings that are wrapped in containers (e.g. MP3, OGG_OPUS).
     * For LINEAR16 audio, we include the WAV header. Note: as
     * with all bytes fields, protobuffers use a pure binary representation,
     * whereas JSON representations use base64.
     *
     * Generated from protobuf field <code>bytes audio_content = 1;</code>
     * @return string
     */
    public function getAudioContent()
    {
        return $this->audio_content;
    }

    /**
     * The audio data bytes encoded as specified in the request, including the
     * header for encodings that are wrapped in containers (e.g. MP3, OGG_OPUS).
     * For LINEAR16 audio, we include the WAV header. Note: as
     * with all bytes fields, protobuffers use a pure binary representation,
     * whereas JSON representations use base64.
     *
     * Generated from protobuf field <code>bytes audio_content = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setAudioContent($var)
    {
        GPBUtil::checkString($var, False);
        $this->audio_content = $var;

        return $this;
    }

}

