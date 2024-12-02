<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/pubsub/v1/pubsub.proto

namespace Google\Cloud\PubSub\V1\StreamingPullResponse;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Acknowledgement IDs sent in one or more previous requests to acknowledge a
 * previously received message.
 *
 * Generated from protobuf message <code>google.pubsub.v1.StreamingPullResponse.AcknowledgeConfirmation</code>
 */
class AcknowledgeConfirmation extends \Google\Protobuf\Internal\Message
{
    /**
     * Successfully processed acknowledgement IDs.
     *
     * Generated from protobuf field <code>repeated string ack_ids = 1 [ctype = CORD];</code>
     */
    private $ack_ids;
    /**
     * List of acknowledgement IDs that were malformed or whose acknowledgement
     * deadline has expired.
     *
     * Generated from protobuf field <code>repeated string invalid_ack_ids = 2 [ctype = CORD];</code>
     */
    private $invalid_ack_ids;
    /**
     * List of acknowledgement IDs that were out of order.
     *
     * Generated from protobuf field <code>repeated string unordered_ack_ids = 3 [ctype = CORD];</code>
     */
    private $unordered_ack_ids;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $ack_ids
     *           Successfully processed acknowledgement IDs.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $invalid_ack_ids
     *           List of acknowledgement IDs that were malformed or whose acknowledgement
     *           deadline has expired.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $unordered_ack_ids
     *           List of acknowledgement IDs that were out of order.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Pubsub\V1\Pubsub::initOnce();
        parent::__construct($data);
    }

    /**
     * Successfully processed acknowledgement IDs.
     *
     * Generated from protobuf field <code>repeated string ack_ids = 1 [ctype = CORD];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAckIds()
    {
        return $this->ack_ids;
    }

    /**
     * Successfully processed acknowledgement IDs.
     *
     * Generated from protobuf field <code>repeated string ack_ids = 1 [ctype = CORD];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAckIds($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->ack_ids = $arr;

        return $this;
    }

    /**
     * List of acknowledgement IDs that were malformed or whose acknowledgement
     * deadline has expired.
     *
     * Generated from protobuf field <code>repeated string invalid_ack_ids = 2 [ctype = CORD];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInvalidAckIds()
    {
        return $this->invalid_ack_ids;
    }

    /**
     * List of acknowledgement IDs that were malformed or whose acknowledgement
     * deadline has expired.
     *
     * Generated from protobuf field <code>repeated string invalid_ack_ids = 2 [ctype = CORD];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInvalidAckIds($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->invalid_ack_ids = $arr;

        return $this;
    }

    /**
     * List of acknowledgement IDs that were out of order.
     *
     * Generated from protobuf field <code>repeated string unordered_ack_ids = 3 [ctype = CORD];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getUnorderedAckIds()
    {
        return $this->unordered_ack_ids;
    }

    /**
     * List of acknowledgement IDs that were out of order.
     *
     * Generated from protobuf field <code>repeated string unordered_ack_ids = 3 [ctype = CORD];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setUnorderedAckIds($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->unordered_ack_ids = $arr;

        return $this;
    }

}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AcknowledgeConfirmation::class, \Google\Cloud\PubSub\V1\StreamingPullResponse_AcknowledgeConfirmation::class);

