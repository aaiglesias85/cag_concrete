<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/security/privateca/v1beta1/resources.proto

namespace Google\Cloud\Security\PrivateCA\V1beta1\CertificateDescription;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A group of fingerprints for the x509 certificate.
 *
 * Generated from protobuf message <code>google.cloud.security.privateca.v1beta1.CertificateDescription.CertificateFingerprint</code>
 */
class CertificateFingerprint extends \Google\Protobuf\Internal\Message
{
    /**
     * The SHA 256 hash, encoded in hexadecimal, of the DER x509 certificate.
     *
     * Generated from protobuf field <code>string sha256_hash = 1;</code>
     */
    private $sha256_hash = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $sha256_hash
     *           The SHA 256 hash, encoded in hexadecimal, of the DER x509 certificate.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Security\Privateca\V1Beta1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * The SHA 256 hash, encoded in hexadecimal, of the DER x509 certificate.
     *
     * Generated from protobuf field <code>string sha256_hash = 1;</code>
     * @return string
     */
    public function getSha256Hash()
    {
        return $this->sha256_hash;
    }

    /**
     * The SHA 256 hash, encoded in hexadecimal, of the DER x509 certificate.
     *
     * Generated from protobuf field <code>string sha256_hash = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSha256Hash($var)
    {
        GPBUtil::checkString($var, True);
        $this->sha256_hash = $var;

        return $this;
    }

}


