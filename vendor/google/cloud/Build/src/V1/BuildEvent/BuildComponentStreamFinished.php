<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/build/v1/build_events.proto

namespace Google\Cloud\Build\V1\BuildEvent;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Notification of the end of a build event stream published by a build
 * component other than CONTROLLER (See StreamId.BuildComponents).
 *
 * Generated from protobuf message <code>google.devtools.build.v1.BuildEvent.BuildComponentStreamFinished</code>
 */
class BuildComponentStreamFinished extends \Google\Protobuf\Internal\Message
{
    /**
     * How the event stream finished.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildEvent.BuildComponentStreamFinished.FinishType type = 1;</code>
     */
    private $type = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $type
     *           How the event stream finished.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Build\V1\BuildEvents::initOnce();
        parent::__construct($data);
    }

    /**
     * How the event stream finished.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildEvent.BuildComponentStreamFinished.FinishType type = 1;</code>
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * How the event stream finished.
     *
     * Generated from protobuf field <code>.google.devtools.build.v1.BuildEvent.BuildComponentStreamFinished.FinishType type = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Build\V1\BuildEvent\BuildComponentStreamFinished\FinishType::class);
        $this->type = $var;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BuildComponentStreamFinished::class, \Google\Cloud\Build\V1\BuildEvent_BuildComponentStreamFinished::class);

