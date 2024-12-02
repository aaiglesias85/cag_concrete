<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dataplex/v1/analyze.proto

namespace Google\Cloud\Dataplex\V1\Content;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Configuration for the Sql Script content.
 *
 * Generated from protobuf message <code>google.cloud.dataplex.v1.Content.SqlScript</code>
 */
class SqlScript extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Query Engine to be used for the Sql Query.
     *
     * Generated from protobuf field <code>.google.cloud.dataplex.v1.Content.SqlScript.QueryEngine engine = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $engine = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $engine
     *           Required. Query Engine to be used for the Sql Query.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dataplex\V1\Analyze::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Query Engine to be used for the Sql Query.
     *
     * Generated from protobuf field <code>.google.cloud.dataplex.v1.Content.SqlScript.QueryEngine engine = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return int
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Required. Query Engine to be used for the Sql Query.
     *
     * Generated from protobuf field <code>.google.cloud.dataplex.v1.Content.SqlScript.QueryEngine engine = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param int $var
     * @return $this
     */
    public function setEngine($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Dataplex\V1\Content\SqlScript\QueryEngine::class);
        $this->engine = $var;

        return $this;
    }

}


