<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/retail/v2/import_config.proto

namespace Google\Cloud\Retail\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * BigQuery source import data from.
 *
 * Generated from protobuf message <code>google.cloud.retail.v2.BigQuerySource</code>
 */
class BigQuerySource extends \Google\Protobuf\Internal\Message
{
    /**
     * The project ID (can be project # or ID) that the BigQuery source is in with
     * a length limit of 128 characters. If not specified, inherits the project
     * ID from the parent request.
     *
     * Generated from protobuf field <code>string project_id = 5;</code>
     */
    private $project_id = '';
    /**
     * Required. The BigQuery data set to copy the data from with a length limit
     * of 1,024 characters.
     *
     * Generated from protobuf field <code>string dataset_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $dataset_id = '';
    /**
     * Required. The BigQuery table to copy the data from with a length limit of
     * 1,024 characters.
     *
     * Generated from protobuf field <code>string table_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $table_id = '';
    /**
     * Intermediate Cloud Storage directory used for the import with a length
     * limit of 2,000 characters. Can be specified if one wants to have the
     * BigQuery export to a specific Cloud Storage directory.
     *
     * Generated from protobuf field <code>string gcs_staging_dir = 3;</code>
     */
    private $gcs_staging_dir = '';
    /**
     * The schema to use when parsing the data from the source.
     * Supported values for product imports:
     * * `product` (default): One JSON [Product][google.cloud.retail.v2.Product]
     * per line. Each product must
     *   have a valid [Product.id][google.cloud.retail.v2.Product.id].
     * * `product_merchant_center`: See [Importing catalog data from Merchant
     *   Center](https://cloud.google.com/retail/recommendations-ai/docs/upload-catalog#mc).
     * Supported values for user events imports:
     * * `user_event` (default): One JSON
     * [UserEvent][google.cloud.retail.v2.UserEvent] per line.
     * * `user_event_ga360`:
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/3437719.
     * * `user_event_ga4`: This feature is in private preview. Please contact the
     *   support team for importing Google Analytics 4 events.
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/7029846.
     * Supported values for auto-completion imports:
     * * `suggestions` (default): One JSON completion suggestion per line.
     * * `denylist`:  One JSON deny suggestion per line.
     * * `allowlist`:  One JSON allow suggestion per line.
     *
     * Generated from protobuf field <code>string data_schema = 4;</code>
     */
    private $data_schema = '';
    protected $partition;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Type\Date $partition_date
     *           BigQuery time partitioned table's _PARTITIONDATE in YYYY-MM-DD format.
     *           Only supported when
     *           [ImportProductsRequest.reconciliation_mode][google.cloud.retail.v2.ImportProductsRequest.reconciliation_mode]
     *           is set to `FULL`.
     *     @type string $project_id
     *           The project ID (can be project # or ID) that the BigQuery source is in with
     *           a length limit of 128 characters. If not specified, inherits the project
     *           ID from the parent request.
     *     @type string $dataset_id
     *           Required. The BigQuery data set to copy the data from with a length limit
     *           of 1,024 characters.
     *     @type string $table_id
     *           Required. The BigQuery table to copy the data from with a length limit of
     *           1,024 characters.
     *     @type string $gcs_staging_dir
     *           Intermediate Cloud Storage directory used for the import with a length
     *           limit of 2,000 characters. Can be specified if one wants to have the
     *           BigQuery export to a specific Cloud Storage directory.
     *     @type string $data_schema
     *           The schema to use when parsing the data from the source.
     *           Supported values for product imports:
     *           * `product` (default): One JSON [Product][google.cloud.retail.v2.Product]
     *           per line. Each product must
     *             have a valid [Product.id][google.cloud.retail.v2.Product.id].
     *           * `product_merchant_center`: See [Importing catalog data from Merchant
     *             Center](https://cloud.google.com/retail/recommendations-ai/docs/upload-catalog#mc).
     *           Supported values for user events imports:
     *           * `user_event` (default): One JSON
     *           [UserEvent][google.cloud.retail.v2.UserEvent] per line.
     *           * `user_event_ga360`:
     *             The schema is available here:
     *             https://support.google.com/analytics/answer/3437719.
     *           * `user_event_ga4`: This feature is in private preview. Please contact the
     *             support team for importing Google Analytics 4 events.
     *             The schema is available here:
     *             https://support.google.com/analytics/answer/7029846.
     *           Supported values for auto-completion imports:
     *           * `suggestions` (default): One JSON completion suggestion per line.
     *           * `denylist`:  One JSON deny suggestion per line.
     *           * `allowlist`:  One JSON allow suggestion per line.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Retail\V2\ImportConfig::initOnce();
        parent::__construct($data);
    }

    /**
     * BigQuery time partitioned table's _PARTITIONDATE in YYYY-MM-DD format.
     * Only supported when
     * [ImportProductsRequest.reconciliation_mode][google.cloud.retail.v2.ImportProductsRequest.reconciliation_mode]
     * is set to `FULL`.
     *
     * Generated from protobuf field <code>.google.type.Date partition_date = 6;</code>
     * @return \Google\Type\Date|null
     */
    public function getPartitionDate()
    {
        return $this->readOneof(6);
    }

    public function hasPartitionDate()
    {
        return $this->hasOneof(6);
    }

    /**
     * BigQuery time partitioned table's _PARTITIONDATE in YYYY-MM-DD format.
     * Only supported when
     * [ImportProductsRequest.reconciliation_mode][google.cloud.retail.v2.ImportProductsRequest.reconciliation_mode]
     * is set to `FULL`.
     *
     * Generated from protobuf field <code>.google.type.Date partition_date = 6;</code>
     * @param \Google\Type\Date $var
     * @return $this
     */
    public function setPartitionDate($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Date::class);
        $this->writeOneof(6, $var);

        return $this;
    }

    /**
     * The project ID (can be project # or ID) that the BigQuery source is in with
     * a length limit of 128 characters. If not specified, inherits the project
     * ID from the parent request.
     *
     * Generated from protobuf field <code>string project_id = 5;</code>
     * @return string
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * The project ID (can be project # or ID) that the BigQuery source is in with
     * a length limit of 128 characters. If not specified, inherits the project
     * ID from the parent request.
     *
     * Generated from protobuf field <code>string project_id = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setProjectId($var)
    {
        GPBUtil::checkString($var, True);
        $this->project_id = $var;

        return $this;
    }

    /**
     * Required. The BigQuery data set to copy the data from with a length limit
     * of 1,024 characters.
     *
     * Generated from protobuf field <code>string dataset_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getDatasetId()
    {
        return $this->dataset_id;
    }

    /**
     * Required. The BigQuery data set to copy the data from with a length limit
     * of 1,024 characters.
     *
     * Generated from protobuf field <code>string dataset_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setDatasetId($var)
    {
        GPBUtil::checkString($var, True);
        $this->dataset_id = $var;

        return $this;
    }

    /**
     * Required. The BigQuery table to copy the data from with a length limit of
     * 1,024 characters.
     *
     * Generated from protobuf field <code>string table_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getTableId()
    {
        return $this->table_id;
    }

    /**
     * Required. The BigQuery table to copy the data from with a length limit of
     * 1,024 characters.
     *
     * Generated from protobuf field <code>string table_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setTableId($var)
    {
        GPBUtil::checkString($var, True);
        $this->table_id = $var;

        return $this;
    }

    /**
     * Intermediate Cloud Storage directory used for the import with a length
     * limit of 2,000 characters. Can be specified if one wants to have the
     * BigQuery export to a specific Cloud Storage directory.
     *
     * Generated from protobuf field <code>string gcs_staging_dir = 3;</code>
     * @return string
     */
    public function getGcsStagingDir()
    {
        return $this->gcs_staging_dir;
    }

    /**
     * Intermediate Cloud Storage directory used for the import with a length
     * limit of 2,000 characters. Can be specified if one wants to have the
     * BigQuery export to a specific Cloud Storage directory.
     *
     * Generated from protobuf field <code>string gcs_staging_dir = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setGcsStagingDir($var)
    {
        GPBUtil::checkString($var, True);
        $this->gcs_staging_dir = $var;

        return $this;
    }

    /**
     * The schema to use when parsing the data from the source.
     * Supported values for product imports:
     * * `product` (default): One JSON [Product][google.cloud.retail.v2.Product]
     * per line. Each product must
     *   have a valid [Product.id][google.cloud.retail.v2.Product.id].
     * * `product_merchant_center`: See [Importing catalog data from Merchant
     *   Center](https://cloud.google.com/retail/recommendations-ai/docs/upload-catalog#mc).
     * Supported values for user events imports:
     * * `user_event` (default): One JSON
     * [UserEvent][google.cloud.retail.v2.UserEvent] per line.
     * * `user_event_ga360`:
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/3437719.
     * * `user_event_ga4`: This feature is in private preview. Please contact the
     *   support team for importing Google Analytics 4 events.
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/7029846.
     * Supported values for auto-completion imports:
     * * `suggestions` (default): One JSON completion suggestion per line.
     * * `denylist`:  One JSON deny suggestion per line.
     * * `allowlist`:  One JSON allow suggestion per line.
     *
     * Generated from protobuf field <code>string data_schema = 4;</code>
     * @return string
     */
    public function getDataSchema()
    {
        return $this->data_schema;
    }

    /**
     * The schema to use when parsing the data from the source.
     * Supported values for product imports:
     * * `product` (default): One JSON [Product][google.cloud.retail.v2.Product]
     * per line. Each product must
     *   have a valid [Product.id][google.cloud.retail.v2.Product.id].
     * * `product_merchant_center`: See [Importing catalog data from Merchant
     *   Center](https://cloud.google.com/retail/recommendations-ai/docs/upload-catalog#mc).
     * Supported values for user events imports:
     * * `user_event` (default): One JSON
     * [UserEvent][google.cloud.retail.v2.UserEvent] per line.
     * * `user_event_ga360`:
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/3437719.
     * * `user_event_ga4`: This feature is in private preview. Please contact the
     *   support team for importing Google Analytics 4 events.
     *   The schema is available here:
     *   https://support.google.com/analytics/answer/7029846.
     * Supported values for auto-completion imports:
     * * `suggestions` (default): One JSON completion suggestion per line.
     * * `denylist`:  One JSON deny suggestion per line.
     * * `allowlist`:  One JSON allow suggestion per line.
     *
     * Generated from protobuf field <code>string data_schema = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setDataSchema($var)
    {
        GPBUtil::checkString($var, True);
        $this->data_schema = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getPartition()
    {
        return $this->whichOneof("partition");
    }

}

