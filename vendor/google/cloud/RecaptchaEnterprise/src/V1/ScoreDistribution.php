<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recaptchaenterprise/v1/recaptchaenterprise.proto

namespace Google\Cloud\RecaptchaEnterprise\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Score distribution.
 *
 * Generated from protobuf message <code>google.cloud.recaptchaenterprise.v1.ScoreDistribution</code>
 */
class ScoreDistribution extends \Google\Protobuf\Internal\Message
{
    /**
     * Map key is score value multiplied by 100. The scores are discrete values
     * between [0, 1]. The maximum number of buckets is on order of a few dozen,
     * but typically much lower (ie. 10).
     *
     * Generated from protobuf field <code>map<int32, int64> score_buckets = 1;</code>
     */
    private $score_buckets;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $score_buckets
     *           Map key is score value multiplied by 100. The scores are discrete values
     *           between [0, 1]. The maximum number of buckets is on order of a few dozen,
     *           but typically much lower (ie. 10).
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Recaptchaenterprise\V1\Recaptchaenterprise::initOnce();
        parent::__construct($data);
    }

    /**
     * Map key is score value multiplied by 100. The scores are discrete values
     * between [0, 1]. The maximum number of buckets is on order of a few dozen,
     * but typically much lower (ie. 10).
     *
     * Generated from protobuf field <code>map<int32, int64> score_buckets = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getScoreBuckets()
    {
        return $this->score_buckets;
    }

    /**
     * Map key is score value multiplied by 100. The scores are discrete values
     * between [0, 1]. The maximum number of buckets is on order of a few dozen,
     * but typically much lower (ie. 10).
     *
     * Generated from protobuf field <code>map<int32, int64> score_buckets = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setScoreBuckets($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::INT32, \Google\Protobuf\Internal\GPBType::INT64);
        $this->score_buckets = $arr;

        return $this;
    }

}

