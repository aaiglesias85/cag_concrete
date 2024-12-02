<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/sql/v1beta4/cloud_sql_resources.proto

namespace Google\Cloud\Sql\V1beta4\ImportContext;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>google.cloud.sql.v1beta4.ImportContext.SqlBakImportOptions</code>
 */
class SqlBakImportOptions extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.ImportContext.SqlBakImportOptions.EncryptionOptions encryption_options = 1;</code>
     */
    private $encryption_options = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Sql\V1beta4\ImportContext\SqlBakImportOptions\EncryptionOptions $encryption_options
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Sql\V1Beta4\CloudSqlResources::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.ImportContext.SqlBakImportOptions.EncryptionOptions encryption_options = 1;</code>
     * @return \Google\Cloud\Sql\V1beta4\ImportContext\SqlBakImportOptions\EncryptionOptions|null
     */
    public function getEncryptionOptions()
    {
        return $this->encryption_options;
    }

    public function hasEncryptionOptions()
    {
        return isset($this->encryption_options);
    }

    public function clearEncryptionOptions()
    {
        unset($this->encryption_options);
    }

    /**
     * Generated from protobuf field <code>.google.cloud.sql.v1beta4.ImportContext.SqlBakImportOptions.EncryptionOptions encryption_options = 1;</code>
     * @param \Google\Cloud\Sql\V1beta4\ImportContext\SqlBakImportOptions\EncryptionOptions $var
     * @return $this
     */
    public function setEncryptionOptions($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Sql\V1beta4\ImportContext\SqlBakImportOptions\EncryptionOptions::class);
        $this->encryption_options = $var;

        return $this;
    }

}


