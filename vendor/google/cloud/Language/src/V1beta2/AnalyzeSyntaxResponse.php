<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/language/v1beta2/language_service.proto

namespace Google\Cloud\Language\V1beta2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The syntax analysis response message.
 *
 * Generated from protobuf message <code>google.cloud.language.v1beta2.AnalyzeSyntaxResponse</code>
 */
class AnalyzeSyntaxResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Sentences in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Sentence sentences = 1;</code>
     */
    private $sentences;
    /**
     * Tokens, along with their syntactic information, in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Token tokens = 2;</code>
     */
    private $tokens;
    /**
     * The language of the text, which will be the same as the language specified
     * in the request or, if not specified, the automatically-detected language.
     * See [Document.language][google.cloud.language.v1beta2.Document.language] field for more details.
     *
     * Generated from protobuf field <code>string language = 3;</code>
     */
    private $language = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Language\V1beta2\Sentence[]|\Google\Protobuf\Internal\RepeatedField $sentences
     *           Sentences in the input document.
     *     @type \Google\Cloud\Language\V1beta2\Token[]|\Google\Protobuf\Internal\RepeatedField $tokens
     *           Tokens, along with their syntactic information, in the input document.
     *     @type string $language
     *           The language of the text, which will be the same as the language specified
     *           in the request or, if not specified, the automatically-detected language.
     *           See [Document.language][google.cloud.language.v1beta2.Document.language] field for more details.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Language\V1Beta2\LanguageService::initOnce();
        parent::__construct($data);
    }

    /**
     * Sentences in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Sentence sentences = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSentences()
    {
        return $this->sentences;
    }

    /**
     * Sentences in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Sentence sentences = 1;</code>
     * @param \Google\Cloud\Language\V1beta2\Sentence[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSentences($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Language\V1beta2\Sentence::class);
        $this->sentences = $arr;

        return $this;
    }

    /**
     * Tokens, along with their syntactic information, in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Token tokens = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Tokens, along with their syntactic information, in the input document.
     *
     * Generated from protobuf field <code>repeated .google.cloud.language.v1beta2.Token tokens = 2;</code>
     * @param \Google\Cloud\Language\V1beta2\Token[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTokens($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Language\V1beta2\Token::class);
        $this->tokens = $arr;

        return $this;
    }

    /**
     * The language of the text, which will be the same as the language specified
     * in the request or, if not specified, the automatically-detected language.
     * See [Document.language][google.cloud.language.v1beta2.Document.language] field for more details.
     *
     * Generated from protobuf field <code>string language = 3;</code>
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * The language of the text, which will be the same as the language specified
     * in the request or, if not specified, the automatically-detected language.
     * See [Document.language][google.cloud.language.v1beta2.Document.language] field for more details.
     *
     * Generated from protobuf field <code>string language = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setLanguage($var)
    {
        GPBUtil::checkString($var, True);
        $this->language = $var;

        return $this;
    }

}

