<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dataplex/v1/metadata.proto

namespace Google\Cloud\Dataplex\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * List metadata entities response.
 *
 * Generated from protobuf message <code>google.cloud.dataplex.v1.ListEntitiesResponse</code>
 */
class ListEntitiesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Entities in the specified parent zone.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dataplex.v1.Entity entities = 1;</code>
     */
    private $entities;
    /**
     * Token to retrieve the next page of results, or empty if there are no
     * remaining results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dataplex\V1\Entity[]|\Google\Protobuf\Internal\RepeatedField $entities
     *           Entities in the specified parent zone.
     *     @type string $next_page_token
     *           Token to retrieve the next page of results, or empty if there are no
     *           remaining results in the list.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Dataplex\V1\Metadata::initOnce();
        parent::__construct($data);
    }

    /**
     * Entities in the specified parent zone.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dataplex.v1.Entity entities = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Entities in the specified parent zone.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dataplex.v1.Entity entities = 1;</code>
     * @param \Google\Cloud\Dataplex\V1\Entity[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setEntities($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dataplex\V1\Entity::class);
        $this->entities = $arr;

        return $this;
    }

    /**
     * Token to retrieve the next page of results, or empty if there are no
     * remaining results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }

    /**
     * Token to retrieve the next page of results, or empty if there are no
     * remaining results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;

        return $this;
    }

}

