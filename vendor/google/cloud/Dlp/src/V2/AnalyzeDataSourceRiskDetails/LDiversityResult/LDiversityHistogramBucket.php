<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/privacy/dlp/v2/dlp.proto

namespace Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Histogram of l-diversity equivalence class sensitive value frequencies.
 *
 * Generated from protobuf message <code>google.privacy.dlp.v2.AnalyzeDataSourceRiskDetails.LDiversityResult.LDiversityHistogramBucket</code>
 */
class LDiversityHistogramBucket extends \Google\Protobuf\Internal\Message
{
    /**
     * Lower bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_lower_bound = 1;</code>
     */
    private $sensitive_value_frequency_lower_bound = 0;
    /**
     * Upper bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_upper_bound = 2;</code>
     */
    private $sensitive_value_frequency_upper_bound = 0;
    /**
     * Total number of equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_size = 3;</code>
     */
    private $bucket_size = 0;
    /**
     * Sample of equivalence classes in this bucket. The total number of
     * classes returned per bucket is capped at 20.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.AnalyzeDataSourceRiskDetails.LDiversityResult.LDiversityEquivalenceClass bucket_values = 4;</code>
     */
    private $bucket_values;
    /**
     * Total number of distinct equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_value_count = 5;</code>
     */
    private $bucket_value_count = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $sensitive_value_frequency_lower_bound
     *           Lower bound on the sensitive value frequencies of the equivalence
     *           classes in this bucket.
     *     @type int|string $sensitive_value_frequency_upper_bound
     *           Upper bound on the sensitive value frequencies of the equivalence
     *           classes in this bucket.
     *     @type int|string $bucket_size
     *           Total number of equivalence classes in this bucket.
     *     @type \Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult\LDiversityEquivalenceClass[]|\Google\Protobuf\Internal\RepeatedField $bucket_values
     *           Sample of equivalence classes in this bucket. The total number of
     *           classes returned per bucket is capped at 20.
     *     @type int|string $bucket_value_count
     *           Total number of distinct equivalence classes in this bucket.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Privacy\Dlp\V2\Dlp::initOnce();
        parent::__construct($data);
    }

    /**
     * Lower bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_lower_bound = 1;</code>
     * @return int|string
     */
    public function getSensitiveValueFrequencyLowerBound()
    {
        return $this->sensitive_value_frequency_lower_bound;
    }

    /**
     * Lower bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_lower_bound = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setSensitiveValueFrequencyLowerBound($var)
    {
        GPBUtil::checkInt64($var);
        $this->sensitive_value_frequency_lower_bound = $var;

        return $this;
    }

    /**
     * Upper bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_upper_bound = 2;</code>
     * @return int|string
     */
    public function getSensitiveValueFrequencyUpperBound()
    {
        return $this->sensitive_value_frequency_upper_bound;
    }

    /**
     * Upper bound on the sensitive value frequencies of the equivalence
     * classes in this bucket.
     *
     * Generated from protobuf field <code>int64 sensitive_value_frequency_upper_bound = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setSensitiveValueFrequencyUpperBound($var)
    {
        GPBUtil::checkInt64($var);
        $this->sensitive_value_frequency_upper_bound = $var;

        return $this;
    }

    /**
     * Total number of equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_size = 3;</code>
     * @return int|string
     */
    public function getBucketSize()
    {
        return $this->bucket_size;
    }

    /**
     * Total number of equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_size = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setBucketSize($var)
    {
        GPBUtil::checkInt64($var);
        $this->bucket_size = $var;

        return $this;
    }

    /**
     * Sample of equivalence classes in this bucket. The total number of
     * classes returned per bucket is capped at 20.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.AnalyzeDataSourceRiskDetails.LDiversityResult.LDiversityEquivalenceClass bucket_values = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getBucketValues()
    {
        return $this->bucket_values;
    }

    /**
     * Sample of equivalence classes in this bucket. The total number of
     * classes returned per bucket is capped at 20.
     *
     * Generated from protobuf field <code>repeated .google.privacy.dlp.v2.AnalyzeDataSourceRiskDetails.LDiversityResult.LDiversityEquivalenceClass bucket_values = 4;</code>
     * @param \Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult\LDiversityEquivalenceClass[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setBucketValues($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails\LDiversityResult\LDiversityEquivalenceClass::class);
        $this->bucket_values = $arr;

        return $this;
    }

    /**
     * Total number of distinct equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_value_count = 5;</code>
     * @return int|string
     */
    public function getBucketValueCount()
    {
        return $this->bucket_value_count;
    }

    /**
     * Total number of distinct equivalence classes in this bucket.
     *
     * Generated from protobuf field <code>int64 bucket_value_count = 5;</code>
     * @param int|string $var
     * @return $this
     */
    public function setBucketValueCount($var)
    {
        GPBUtil::checkInt64($var);
        $this->bucket_value_count = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LDiversityHistogramBucket::class, \Google\Cloud\Dlp\V2\AnalyzeDataSourceRiskDetails_LDiversityResult_LDiversityHistogramBucket::class);

