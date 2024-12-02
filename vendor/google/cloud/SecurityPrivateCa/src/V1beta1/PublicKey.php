<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/security/privateca/v1beta1/resources.proto

namespace Google\Cloud\Security\PrivateCA\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A [PublicKey][google.cloud.security.privateca.v1beta1.PublicKey] describes a public key.
 *
 * Generated from protobuf message <code>google.cloud.security.privateca.v1beta1.PublicKey</code>
 */
class PublicKey extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The type of public key.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1beta1.PublicKey.KeyType type = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $type = 0;
    /**
     * Required. A public key. Padding and encoding varies by 'KeyType' and is described
     * along with the KeyType values.
     *
     * Generated from protobuf field <code>bytes key = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $key = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $type
     *           Required. The type of public key.
     *     @type string $key
     *           Required. A public key. Padding and encoding varies by 'KeyType' and is described
     *           along with the KeyType values.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Security\Privateca\V1Beta1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The type of public key.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1beta1.PublicKey.KeyType type = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Required. The type of public key.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1beta1.PublicKey.KeyType type = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param int $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Security\PrivateCA\V1beta1\PublicKey\KeyType::class);
        $this->type = $var;

        return $this;
    }

    /**
     * Required. A public key. Padding and encoding varies by 'KeyType' and is described
     * along with the KeyType values.
     *
     * Generated from protobuf field <code>bytes key = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Required. A public key. Padding and encoding varies by 'KeyType' and is described
     * along with the KeyType values.
     *
     * Generated from protobuf field <code>bytes key = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setKey($var)
    {
        GPBUtil::checkString($var, False);
        $this->key = $var;

        return $this;
    }

}

