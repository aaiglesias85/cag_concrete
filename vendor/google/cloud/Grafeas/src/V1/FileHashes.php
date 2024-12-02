<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grafeas/v1/provenance.proto

namespace Grafeas\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Container message for hashes of byte content of files, used in source
 * messages to verify integrity of source input to the build.
 *
 * Generated from protobuf message <code>grafeas.v1.FileHashes</code>
 */
class FileHashes extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Collection of file hashes.
     *
     * Generated from protobuf field <code>repeated .grafeas.v1.Hash file_hash = 1;</code>
     */
    private $file_hash;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Grafeas\V1\Hash[]|\Google\Protobuf\Internal\RepeatedField $file_hash
     *           Required. Collection of file hashes.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Grafeas\V1\Provenance::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Collection of file hashes.
     *
     * Generated from protobuf field <code>repeated .grafeas.v1.Hash file_hash = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getFileHash()
    {
        return $this->file_hash;
    }

    /**
     * Required. Collection of file hashes.
     *
     * Generated from protobuf field <code>repeated .grafeas.v1.Hash file_hash = 1;</code>
     * @param \Grafeas\V1\Hash[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setFileHash($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Grafeas\V1\Hash::class);
        $this->file_hash = $arr;

        return $this;
    }

}

