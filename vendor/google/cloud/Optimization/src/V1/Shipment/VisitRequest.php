<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/optimization/v1/fleet_routing.proto

namespace Google\Cloud\Optimization\V1\Shipment;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request for a visit which can be done by a vehicle: it has a geo-location
 * (or two, see below), opening and closing times represented by time windows,
 * and a service duration time (time spent by the vehicle once it has arrived
 * to pickup or drop off goods).
 *
 * Generated from protobuf message <code>google.cloud.optimization.v1.Shipment.VisitRequest</code>
 */
class VisitRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * The geo-location where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng arrival_location = 1;</code>
     */
    private $arrival_location = null;
    /**
     * The waypoint where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint arrival_waypoint = 2;</code>
     */
    private $arrival_waypoint = null;
    /**
     * The geo-location where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_location`.
     * If the shipment model has duration distance matrices,
     * `departure_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng departure_location = 3;</code>
     */
    private $departure_location = null;
    /**
     * The waypoint where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_waypoint`.
     * If the shipment model has duration distance matrices,
     * `departure_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint departure_waypoint = 4;</code>
     */
    private $departure_waypoint = null;
    /**
     * Specifies tags attached to the visit request.
     * Empty or duplicate strings are not allowed.
     *
     * Generated from protobuf field <code>repeated string tags = 5;</code>
     */
    private $tags;
    /**
     * Time windows which constrain the arrival time at a visit.
     * Note that a vehicle may depart outside of the arrival time window, i.e.
     * arrival time + duration do not need to be inside a time window. This can
     * result in waiting time if the vehicle arrives before
     * [TimeWindow.start_time][google.cloud.optimization.v1.TimeWindow.start_time].
     * The absence of `TimeWindow` means that the vehicle can perform this visit
     * at any time.
     * Time windows must be disjoint, i.e. no time window must overlap with or
     * be adjacent to another, and they must be in increasing order.
     * `cost_per_hour_after_soft_end_time` and `soft_end_time` can only
     * be set if there is a single time window.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.TimeWindow time_windows = 6;</code>
     */
    private $time_windows;
    /**
     * Duration of the visit, i.e. time spent by the vehicle between arrival
     * and departure (to be added to the possible waiting time; see
     * `time_windows`).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration duration = 7;</code>
     */
    private $duration = null;
    /**
     * Cost to service this visit request on a vehicle route. This can be used
     * to pay different costs for each alternative pickup or delivery of a
     * shipment. This cost must be in the same unit as `Shipment.penalty_cost`
     * and must not be negative.
     *
     * Generated from protobuf field <code>double cost = 8;</code>
     */
    private $cost = 0.0;
    /**
     * Load demands of this visit request. This is just like
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands] field, except that it only applies to this
     * [VisitRequest][google.cloud.optimization.v1.Shipment.VisitRequest] instead of the whole [Shipment][google.cloud.optimization.v1.Shipment].
     * The demands listed here are added to the demands listed in
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands].
     *
     * Generated from protobuf field <code>map<string, .google.cloud.optimization.v1.Shipment.Load> load_demands = 12;</code>
     */
    private $load_demands;
    /**
     * Specifies the types of the visit. This may be used to allocate additional
     * time required for a vehicle to complete this visit (see
     * [Vehicle.extra_visit_duration_for_visit_type][google.cloud.optimization.v1.Vehicle.extra_visit_duration_for_visit_type]).
     * A type can only appear once.
     *
     * Generated from protobuf field <code>repeated string visit_types = 10;</code>
     */
    private $visit_types;
    /**
     * Specifies a label for this `VisitRequest`. This label is reported in the
     * response as `visit_label` in the corresponding [ShipmentRoute.Visit][google.cloud.optimization.v1.ShipmentRoute.Visit].
     *
     * Generated from protobuf field <code>string label = 11;</code>
     */
    private $label = '';
    /**
     * Deprecated: Use [VisitRequest.load_demands][] instead.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.CapacityQuantity demands = 9 [deprecated = true];</code>
     * @deprecated
     */
    private $demands;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Type\LatLng $arrival_location
     *           The geo-location where the vehicle arrives when performing this
     *           `VisitRequest`. If the shipment model has duration distance matrices,
     *           `arrival_location` must not be specified.
     *     @type \Google\Cloud\Optimization\V1\Waypoint $arrival_waypoint
     *           The waypoint where the vehicle arrives when performing this
     *           `VisitRequest`. If the shipment model has duration distance matrices,
     *           `arrival_waypoint` must not be specified.
     *     @type \Google\Type\LatLng $departure_location
     *           The geo-location where the vehicle departs after completing this
     *           `VisitRequest`. Can be omitted if it is the same as `arrival_location`.
     *           If the shipment model has duration distance matrices,
     *           `departure_location` must not be specified.
     *     @type \Google\Cloud\Optimization\V1\Waypoint $departure_waypoint
     *           The waypoint where the vehicle departs after completing this
     *           `VisitRequest`. Can be omitted if it is the same as `arrival_waypoint`.
     *           If the shipment model has duration distance matrices,
     *           `departure_waypoint` must not be specified.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $tags
     *           Specifies tags attached to the visit request.
     *           Empty or duplicate strings are not allowed.
     *     @type \Google\Cloud\Optimization\V1\TimeWindow[]|\Google\Protobuf\Internal\RepeatedField $time_windows
     *           Time windows which constrain the arrival time at a visit.
     *           Note that a vehicle may depart outside of the arrival time window, i.e.
     *           arrival time + duration do not need to be inside a time window. This can
     *           result in waiting time if the vehicle arrives before
     *           [TimeWindow.start_time][google.cloud.optimization.v1.TimeWindow.start_time].
     *           The absence of `TimeWindow` means that the vehicle can perform this visit
     *           at any time.
     *           Time windows must be disjoint, i.e. no time window must overlap with or
     *           be adjacent to another, and they must be in increasing order.
     *           `cost_per_hour_after_soft_end_time` and `soft_end_time` can only
     *           be set if there is a single time window.
     *     @type \Google\Protobuf\Duration $duration
     *           Duration of the visit, i.e. time spent by the vehicle between arrival
     *           and departure (to be added to the possible waiting time; see
     *           `time_windows`).
     *     @type float $cost
     *           Cost to service this visit request on a vehicle route. This can be used
     *           to pay different costs for each alternative pickup or delivery of a
     *           shipment. This cost must be in the same unit as `Shipment.penalty_cost`
     *           and must not be negative.
     *     @type array|\Google\Protobuf\Internal\MapField $load_demands
     *           Load demands of this visit request. This is just like
     *           [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands] field, except that it only applies to this
     *           [VisitRequest][google.cloud.optimization.v1.Shipment.VisitRequest] instead of the whole [Shipment][google.cloud.optimization.v1.Shipment].
     *           The demands listed here are added to the demands listed in
     *           [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands].
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $visit_types
     *           Specifies the types of the visit. This may be used to allocate additional
     *           time required for a vehicle to complete this visit (see
     *           [Vehicle.extra_visit_duration_for_visit_type][google.cloud.optimization.v1.Vehicle.extra_visit_duration_for_visit_type]).
     *           A type can only appear once.
     *     @type string $label
     *           Specifies a label for this `VisitRequest`. This label is reported in the
     *           response as `visit_label` in the corresponding [ShipmentRoute.Visit][google.cloud.optimization.v1.ShipmentRoute.Visit].
     *     @type \Google\Cloud\Optimization\V1\CapacityQuantity[]|\Google\Protobuf\Internal\RepeatedField $demands
     *           Deprecated: Use [VisitRequest.load_demands][] instead.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Optimization\V1\FleetRouting::initOnce();
        parent::__construct($data);
    }

    /**
     * The geo-location where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng arrival_location = 1;</code>
     * @return \Google\Type\LatLng|null
     */
    public function getArrivalLocation()
    {
        return $this->arrival_location;
    }

    public function hasArrivalLocation()
    {
        return isset($this->arrival_location);
    }

    public function clearArrivalLocation()
    {
        unset($this->arrival_location);
    }

    /**
     * The geo-location where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng arrival_location = 1;</code>
     * @param \Google\Type\LatLng $var
     * @return $this
     */
    public function setArrivalLocation($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\LatLng::class);
        $this->arrival_location = $var;

        return $this;
    }

    /**
     * The waypoint where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint arrival_waypoint = 2;</code>
     * @return \Google\Cloud\Optimization\V1\Waypoint|null
     */
    public function getArrivalWaypoint()
    {
        return $this->arrival_waypoint;
    }

    public function hasArrivalWaypoint()
    {
        return isset($this->arrival_waypoint);
    }

    public function clearArrivalWaypoint()
    {
        unset($this->arrival_waypoint);
    }

    /**
     * The waypoint where the vehicle arrives when performing this
     * `VisitRequest`. If the shipment model has duration distance matrices,
     * `arrival_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint arrival_waypoint = 2;</code>
     * @param \Google\Cloud\Optimization\V1\Waypoint $var
     * @return $this
     */
    public function setArrivalWaypoint($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Optimization\V1\Waypoint::class);
        $this->arrival_waypoint = $var;

        return $this;
    }

    /**
     * The geo-location where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_location`.
     * If the shipment model has duration distance matrices,
     * `departure_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng departure_location = 3;</code>
     * @return \Google\Type\LatLng|null
     */
    public function getDepartureLocation()
    {
        return $this->departure_location;
    }

    public function hasDepartureLocation()
    {
        return isset($this->departure_location);
    }

    public function clearDepartureLocation()
    {
        unset($this->departure_location);
    }

    /**
     * The geo-location where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_location`.
     * If the shipment model has duration distance matrices,
     * `departure_location` must not be specified.
     *
     * Generated from protobuf field <code>.google.type.LatLng departure_location = 3;</code>
     * @param \Google\Type\LatLng $var
     * @return $this
     */
    public function setDepartureLocation($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\LatLng::class);
        $this->departure_location = $var;

        return $this;
    }

    /**
     * The waypoint where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_waypoint`.
     * If the shipment model has duration distance matrices,
     * `departure_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint departure_waypoint = 4;</code>
     * @return \Google\Cloud\Optimization\V1\Waypoint|null
     */
    public function getDepartureWaypoint()
    {
        return $this->departure_waypoint;
    }

    public function hasDepartureWaypoint()
    {
        return isset($this->departure_waypoint);
    }

    public function clearDepartureWaypoint()
    {
        unset($this->departure_waypoint);
    }

    /**
     * The waypoint where the vehicle departs after completing this
     * `VisitRequest`. Can be omitted if it is the same as `arrival_waypoint`.
     * If the shipment model has duration distance matrices,
     * `departure_waypoint` must not be specified.
     *
     * Generated from protobuf field <code>.google.cloud.optimization.v1.Waypoint departure_waypoint = 4;</code>
     * @param \Google\Cloud\Optimization\V1\Waypoint $var
     * @return $this
     */
    public function setDepartureWaypoint($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Optimization\V1\Waypoint::class);
        $this->departure_waypoint = $var;

        return $this;
    }

    /**
     * Specifies tags attached to the visit request.
     * Empty or duplicate strings are not allowed.
     *
     * Generated from protobuf field <code>repeated string tags = 5;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Specifies tags attached to the visit request.
     * Empty or duplicate strings are not allowed.
     *
     * Generated from protobuf field <code>repeated string tags = 5;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTags($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->tags = $arr;

        return $this;
    }

    /**
     * Time windows which constrain the arrival time at a visit.
     * Note that a vehicle may depart outside of the arrival time window, i.e.
     * arrival time + duration do not need to be inside a time window. This can
     * result in waiting time if the vehicle arrives before
     * [TimeWindow.start_time][google.cloud.optimization.v1.TimeWindow.start_time].
     * The absence of `TimeWindow` means that the vehicle can perform this visit
     * at any time.
     * Time windows must be disjoint, i.e. no time window must overlap with or
     * be adjacent to another, and they must be in increasing order.
     * `cost_per_hour_after_soft_end_time` and `soft_end_time` can only
     * be set if there is a single time window.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.TimeWindow time_windows = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTimeWindows()
    {
        return $this->time_windows;
    }

    /**
     * Time windows which constrain the arrival time at a visit.
     * Note that a vehicle may depart outside of the arrival time window, i.e.
     * arrival time + duration do not need to be inside a time window. This can
     * result in waiting time if the vehicle arrives before
     * [TimeWindow.start_time][google.cloud.optimization.v1.TimeWindow.start_time].
     * The absence of `TimeWindow` means that the vehicle can perform this visit
     * at any time.
     * Time windows must be disjoint, i.e. no time window must overlap with or
     * be adjacent to another, and they must be in increasing order.
     * `cost_per_hour_after_soft_end_time` and `soft_end_time` can only
     * be set if there is a single time window.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.TimeWindow time_windows = 6;</code>
     * @param \Google\Cloud\Optimization\V1\TimeWindow[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTimeWindows($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Optimization\V1\TimeWindow::class);
        $this->time_windows = $arr;

        return $this;
    }

    /**
     * Duration of the visit, i.e. time spent by the vehicle between arrival
     * and departure (to be added to the possible waiting time; see
     * `time_windows`).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration duration = 7;</code>
     * @return \Google\Protobuf\Duration|null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    public function hasDuration()
    {
        return isset($this->duration);
    }

    public function clearDuration()
    {
        unset($this->duration);
    }

    /**
     * Duration of the visit, i.e. time spent by the vehicle between arrival
     * and departure (to be added to the possible waiting time; see
     * `time_windows`).
     *
     * Generated from protobuf field <code>.google.protobuf.Duration duration = 7;</code>
     * @param \Google\Protobuf\Duration $var
     * @return $this
     */
    public function setDuration($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Duration::class);
        $this->duration = $var;

        return $this;
    }

    /**
     * Cost to service this visit request on a vehicle route. This can be used
     * to pay different costs for each alternative pickup or delivery of a
     * shipment. This cost must be in the same unit as `Shipment.penalty_cost`
     * and must not be negative.
     *
     * Generated from protobuf field <code>double cost = 8;</code>
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Cost to service this visit request on a vehicle route. This can be used
     * to pay different costs for each alternative pickup or delivery of a
     * shipment. This cost must be in the same unit as `Shipment.penalty_cost`
     * and must not be negative.
     *
     * Generated from protobuf field <code>double cost = 8;</code>
     * @param float $var
     * @return $this
     */
    public function setCost($var)
    {
        GPBUtil::checkDouble($var);
        $this->cost = $var;

        return $this;
    }

    /**
     * Load demands of this visit request. This is just like
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands] field, except that it only applies to this
     * [VisitRequest][google.cloud.optimization.v1.Shipment.VisitRequest] instead of the whole [Shipment][google.cloud.optimization.v1.Shipment].
     * The demands listed here are added to the demands listed in
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands].
     *
     * Generated from protobuf field <code>map<string, .google.cloud.optimization.v1.Shipment.Load> load_demands = 12;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getLoadDemands()
    {
        return $this->load_demands;
    }

    /**
     * Load demands of this visit request. This is just like
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands] field, except that it only applies to this
     * [VisitRequest][google.cloud.optimization.v1.Shipment.VisitRequest] instead of the whole [Shipment][google.cloud.optimization.v1.Shipment].
     * The demands listed here are added to the demands listed in
     * [Shipment.load_demands][google.cloud.optimization.v1.Shipment.load_demands].
     *
     * Generated from protobuf field <code>map<string, .google.cloud.optimization.v1.Shipment.Load> load_demands = 12;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setLoadDemands($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Optimization\V1\Shipment\Load::class);
        $this->load_demands = $arr;

        return $this;
    }

    /**
     * Specifies the types of the visit. This may be used to allocate additional
     * time required for a vehicle to complete this visit (see
     * [Vehicle.extra_visit_duration_for_visit_type][google.cloud.optimization.v1.Vehicle.extra_visit_duration_for_visit_type]).
     * A type can only appear once.
     *
     * Generated from protobuf field <code>repeated string visit_types = 10;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getVisitTypes()
    {
        return $this->visit_types;
    }

    /**
     * Specifies the types of the visit. This may be used to allocate additional
     * time required for a vehicle to complete this visit (see
     * [Vehicle.extra_visit_duration_for_visit_type][google.cloud.optimization.v1.Vehicle.extra_visit_duration_for_visit_type]).
     * A type can only appear once.
     *
     * Generated from protobuf field <code>repeated string visit_types = 10;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setVisitTypes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->visit_types = $arr;

        return $this;
    }

    /**
     * Specifies a label for this `VisitRequest`. This label is reported in the
     * response as `visit_label` in the corresponding [ShipmentRoute.Visit][google.cloud.optimization.v1.ShipmentRoute.Visit].
     *
     * Generated from protobuf field <code>string label = 11;</code>
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Specifies a label for this `VisitRequest`. This label is reported in the
     * response as `visit_label` in the corresponding [ShipmentRoute.Visit][google.cloud.optimization.v1.ShipmentRoute.Visit].
     *
     * Generated from protobuf field <code>string label = 11;</code>
     * @param string $var
     * @return $this
     */
    public function setLabel($var)
    {
        GPBUtil::checkString($var, True);
        $this->label = $var;

        return $this;
    }

    /**
     * Deprecated: Use [VisitRequest.load_demands][] instead.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.CapacityQuantity demands = 9 [deprecated = true];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     * @deprecated
     */
    public function getDemands()
    {
        @trigger_error('demands is deprecated.', E_USER_DEPRECATED);
        return $this->demands;
    }

    /**
     * Deprecated: Use [VisitRequest.load_demands][] instead.
     *
     * Generated from protobuf field <code>repeated .google.cloud.optimization.v1.CapacityQuantity demands = 9 [deprecated = true];</code>
     * @param \Google\Cloud\Optimization\V1\CapacityQuantity[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     * @deprecated
     */
    public function setDemands($var)
    {
        @trigger_error('demands is deprecated.', E_USER_DEPRECATED);
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Optimization\V1\CapacityQuantity::class);
        $this->demands = $arr;

        return $this;
    }

}


