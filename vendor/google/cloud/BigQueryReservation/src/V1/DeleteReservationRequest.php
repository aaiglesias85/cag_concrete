<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/bigquery/reservation/v1/reservation.proto

namespace Google\Cloud\BigQuery\Reservation\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request for [ReservationService.DeleteReservation][google.cloud.bigquery.reservation.v1.ReservationService.DeleteReservation].
 *
 * Generated from protobuf message <code>google.cloud.bigquery.reservation.v1.DeleteReservationRequest</code>
 */
class DeleteReservationRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Resource name of the reservation to retrieve. E.g.,
     *    `projects/myproject/locations/US/reservations/team1-prod`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $name = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Required. Resource name of the reservation to retrieve. E.g.,
     *              `projects/myproject/locations/US/reservations/team1-prod`
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Bigquery\Reservation\V1\Reservation::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Resource name of the reservation to retrieve. E.g.,
     *    `projects/myproject/locations/US/reservations/team1-prod`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Required. Resource name of the reservation to retrieve. E.g.,
     *    `projects/myproject/locations/US/reservations/team1-prod`
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

}

