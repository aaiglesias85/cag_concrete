<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/billing/budgets/v1/budget_model.proto

namespace Google\Cloud\Billing\Budgets\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * All date times begin at 12 AM US and Canadian Pacific Time (UTC-8).
 *
 * Generated from protobuf message <code>google.cloud.billing.budgets.v1.CustomPeriod</code>
 */
class CustomPeriod extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The start date must be after January 1, 2017.
     *
     * Generated from protobuf field <code>.google.type.Date start_date = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $start_date = null;
    /**
     * Optional. The end date of the time period. Budgets with elapsed end date
     * won't be processed. If unset, specifies to track all usage incurred since
     * the start_date.
     *
     * Generated from protobuf field <code>.google.type.Date end_date = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $end_date = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Type\Date $start_date
     *           Required. The start date must be after January 1, 2017.
     *     @type \Google\Type\Date $end_date
     *           Optional. The end date of the time period. Budgets with elapsed end date
     *           won't be processed. If unset, specifies to track all usage incurred since
     *           the start_date.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Billing\Budgets\V1\BudgetModel::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The start date must be after January 1, 2017.
     *
     * Generated from protobuf field <code>.google.type.Date start_date = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Type\Date|null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    public function hasStartDate()
    {
        return isset($this->start_date);
    }

    public function clearStartDate()
    {
        unset($this->start_date);
    }

    /**
     * Required. The start date must be after January 1, 2017.
     *
     * Generated from protobuf field <code>.google.type.Date start_date = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Type\Date $var
     * @return $this
     */
    public function setStartDate($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Date::class);
        $this->start_date = $var;

        return $this;
    }

    /**
     * Optional. The end date of the time period. Budgets with elapsed end date
     * won't be processed. If unset, specifies to track all usage incurred since
     * the start_date.
     *
     * Generated from protobuf field <code>.google.type.Date end_date = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Type\Date|null
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    public function hasEndDate()
    {
        return isset($this->end_date);
    }

    public function clearEndDate()
    {
        unset($this->end_date);
    }

    /**
     * Optional. The end date of the time period. Budgets with elapsed end date
     * won't be processed. If unset, specifies to track all usage incurred since
     * the start_date.
     *
     * Generated from protobuf field <code>.google.type.Date end_date = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Type\Date $var
     * @return $this
     */
    public function setEndDate($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Date::class);
        $this->end_date = $var;

        return $this;
    }

}

