<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grafeas/v1/grafeas.proto

namespace Grafeas\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request to create notes in batch.
 *
 * Generated from protobuf message <code>grafeas.v1.BatchCreateNotesRequest</code>
 */
class BatchCreateNotesRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * The name of the project in the form of `projects/[PROJECT_ID]`, under which
     * the notes are to be created.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * The notes to create. Max allowed length is 1000.
     *
     * Generated from protobuf field <code>map<string, .grafeas.v1.Note> notes = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $notes;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           The name of the project in the form of `projects/[PROJECT_ID]`, under which
     *           the notes are to be created.
     *     @type array|\Google\Protobuf\Internal\MapField $notes
     *           The notes to create. Max allowed length is 1000.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Grafeas\V1\Grafeas::initOnce();
        parent::__construct($data);
    }

    /**
     * The name of the project in the form of `projects/[PROJECT_ID]`, under which
     * the notes are to be created.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * The name of the project in the form of `projects/[PROJECT_ID]`, under which
     * the notes are to be created.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * The notes to create. Max allowed length is 1000.
     *
     * Generated from protobuf field <code>map<string, .grafeas.v1.Note> notes = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * The notes to create. Max allowed length is 1000.
     *
     * Generated from protobuf field <code>map<string, .grafeas.v1.Note> notes = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setNotes($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Grafeas\V1\Note::class);
        $this->notes = $arr;

        return $this;
    }

}

