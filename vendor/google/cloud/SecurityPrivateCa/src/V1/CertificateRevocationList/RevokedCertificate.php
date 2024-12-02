<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/security/privateca/v1/resources.proto

namespace Google\Cloud\Security\PrivateCA\V1\CertificateRevocationList;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Describes a revoked [Certificate][google.cloud.security.privateca.v1.Certificate].
 *
 * Generated from protobuf message <code>google.cloud.security.privateca.v1.CertificateRevocationList.RevokedCertificate</code>
 */
class RevokedCertificate extends \Google\Protobuf\Internal\Message
{
    /**
     * The resource name for the [Certificate][google.cloud.security.privateca.v1.Certificate] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;caPools/&#42;&#47;certificates/&#42;`.
     *
     * Generated from protobuf field <code>string certificate = 1 [(.google.api.resource_reference) = {</code>
     */
    private $certificate = '';
    /**
     * The serial number of the [Certificate][google.cloud.security.privateca.v1.Certificate].
     *
     * Generated from protobuf field <code>string hex_serial_number = 2;</code>
     */
    private $hex_serial_number = '';
    /**
     * The reason the [Certificate][google.cloud.security.privateca.v1.Certificate] was revoked.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1.RevocationReason revocation_reason = 3;</code>
     */
    private $revocation_reason = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $certificate
     *           The resource name for the [Certificate][google.cloud.security.privateca.v1.Certificate] in the format
     *           `projects/&#42;&#47;locations/&#42;&#47;caPools/&#42;&#47;certificates/&#42;`.
     *     @type string $hex_serial_number
     *           The serial number of the [Certificate][google.cloud.security.privateca.v1.Certificate].
     *     @type int $revocation_reason
     *           The reason the [Certificate][google.cloud.security.privateca.v1.Certificate] was revoked.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Security\Privateca\V1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * The resource name for the [Certificate][google.cloud.security.privateca.v1.Certificate] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;caPools/&#42;&#47;certificates/&#42;`.
     *
     * Generated from protobuf field <code>string certificate = 1 [(.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * The resource name for the [Certificate][google.cloud.security.privateca.v1.Certificate] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;caPools/&#42;&#47;certificates/&#42;`.
     *
     * Generated from protobuf field <code>string certificate = 1 [(.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setCertificate($var)
    {
        GPBUtil::checkString($var, True);
        $this->certificate = $var;

        return $this;
    }

    /**
     * The serial number of the [Certificate][google.cloud.security.privateca.v1.Certificate].
     *
     * Generated from protobuf field <code>string hex_serial_number = 2;</code>
     * @return string
     */
    public function getHexSerialNumber()
    {
        return $this->hex_serial_number;
    }

    /**
     * The serial number of the [Certificate][google.cloud.security.privateca.v1.Certificate].
     *
     * Generated from protobuf field <code>string hex_serial_number = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setHexSerialNumber($var)
    {
        GPBUtil::checkString($var, True);
        $this->hex_serial_number = $var;

        return $this;
    }

    /**
     * The reason the [Certificate][google.cloud.security.privateca.v1.Certificate] was revoked.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1.RevocationReason revocation_reason = 3;</code>
     * @return int
     */
    public function getRevocationReason()
    {
        return $this->revocation_reason;
    }

    /**
     * The reason the [Certificate][google.cloud.security.privateca.v1.Certificate] was revoked.
     *
     * Generated from protobuf field <code>.google.cloud.security.privateca.v1.RevocationReason revocation_reason = 3;</code>
     * @param int $var
     * @return $this
     */
    public function setRevocationReason($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Security\PrivateCA\V1\RevocationReason::class);
        $this->revocation_reason = $var;

        return $this;
    }

}


