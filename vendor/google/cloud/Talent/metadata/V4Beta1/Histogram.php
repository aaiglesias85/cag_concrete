<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/talent/v4beta1/histogram.proto

namespace GPBMetadata\Google\Cloud\Talent\V4Beta1;

class Histogram
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Annotations::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
+google/cloud/talent/v4beta1/histogram.protogoogle.cloud.talent.v4beta1")
HistogramQuery
histogram_query (	"�
HistogramQueryResult
histogram_query (	S
	histogram (2@.google.cloud.talent.v4beta1.HistogramQueryResult.HistogramEntry0
HistogramEntry
key (	
value (:8B|
com.google.cloud.talent.v4beta1BHistogramProtoPZAgoogle.golang.org/genproto/googleapis/cloud/talent/v4beta1;talent�CTSbproto3'
        , true);

        static::$is_initialized = true;
    }
}
