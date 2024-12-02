<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/kms/v1/resources.proto

namespace Google\Cloud\Kms\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * An [ImportJob][google.cloud.kms.v1.ImportJob] can be used to create
 * [CryptoKeys][google.cloud.kms.v1.CryptoKey] and
 * [CryptoKeyVersions][google.cloud.kms.v1.CryptoKeyVersion] using pre-existing
 * key material, generated outside of Cloud KMS.
 * When an [ImportJob][google.cloud.kms.v1.ImportJob] is created, Cloud KMS will
 * generate a "wrapping key", which is a public/private key pair. You use the
 * wrapping key to encrypt (also known as wrap) the pre-existing key material to
 * protect it during the import process. The nature of the wrapping key depends
 * on the choice of
 * [import_method][google.cloud.kms.v1.ImportJob.import_method]. When the
 * wrapping key generation is complete, the
 * [state][google.cloud.kms.v1.ImportJob.state] will be set to
 * [ACTIVE][google.cloud.kms.v1.ImportJob.ImportJobState.ACTIVE] and the
 * [public_key][google.cloud.kms.v1.ImportJob.public_key] can be fetched. The
 * fetched public key can then be used to wrap your pre-existing key material.
 * Once the key material is wrapped, it can be imported into a new
 * [CryptoKeyVersion][google.cloud.kms.v1.CryptoKeyVersion] in an existing
 * [CryptoKey][google.cloud.kms.v1.CryptoKey] by calling
 * [ImportCryptoKeyVersion][google.cloud.kms.v1.KeyManagementService.ImportCryptoKeyVersion].
 * Multiple [CryptoKeyVersions][google.cloud.kms.v1.CryptoKeyVersion] can be
 * imported with a single [ImportJob][google.cloud.kms.v1.ImportJob]. Cloud KMS
 * uses the private key portion of the wrapping key to unwrap the key material.
 * Only Cloud KMS has access to the private key.
 * An [ImportJob][google.cloud.kms.v1.ImportJob] expires 3 days after it is
 * created. Once expired, Cloud KMS will no longer be able to import or unwrap
 * any key material that was wrapped with the
 * [ImportJob][google.cloud.kms.v1.ImportJob]'s public key.
 * For more information, see
 * [Importing a key](https://cloud.google.com/kms/docs/importing-a-key).
 *
 * Generated from protobuf message <code>google.cloud.kms.v1.ImportJob</code>
 */
class ImportJob extends \Google\Protobuf\Internal\Message
{
    /**
     * Output only. The resource name for this
     * [ImportJob][google.cloud.kms.v1.ImportJob] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;keyRings/&#42;&#47;importJobs/&#42;`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $name = '';
    /**
     * Required. Immutable. The wrapping method to be used for incoming key
     * material.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportMethod import_method = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     */
    private $import_method = 0;
    /**
     * Required. Immutable. The protection level of the
     * [ImportJob][google.cloud.kms.v1.ImportJob]. This must match the
     * [protection_level][google.cloud.kms.v1.CryptoKeyVersionTemplate.protection_level]
     * of the [version_template][google.cloud.kms.v1.CryptoKey.version_template]
     * on the [CryptoKey][google.cloud.kms.v1.CryptoKey] you attempt to import
     * into.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ProtectionLevel protection_level = 9 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     */
    private $protection_level = 0;
    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $create_time = null;
    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]'s key
     * material was generated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp generate_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $generate_time = null;
    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] is scheduled for expiration and
     * can no longer be used to import key material.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $expire_time = null;
    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]
     * expired. Only present if [state][google.cloud.kms.v1.ImportJob.state] is
     * [EXPIRED][google.cloud.kms.v1.ImportJob.ImportJobState.EXPIRED].
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_event_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $expire_event_time = null;
    /**
     * Output only. The current state of the
     * [ImportJob][google.cloud.kms.v1.ImportJob], indicating if it can be used.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportJobState state = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $state = 0;
    /**
     * Output only. The public key with which to wrap key material prior to
     * import. Only returned if [state][google.cloud.kms.v1.ImportJob.state] is
     * [ACTIVE][google.cloud.kms.v1.ImportJob.ImportJobState.ACTIVE].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.WrappingPublicKey public_key = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $public_key = null;
    /**
     * Output only. Statement that was generated and signed by the key creator
     * (for example, an HSM) at key creation time. Use this statement to verify
     * attributes of the key as stored on the HSM, independently of Google.
     * Only present if the chosen
     * [ImportMethod][google.cloud.kms.v1.ImportJob.ImportMethod] is one with a
     * protection level of [HSM][google.cloud.kms.v1.ProtectionLevel.HSM].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.KeyOperationAttestation attestation = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $attestation = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           Output only. The resource name for this
     *           [ImportJob][google.cloud.kms.v1.ImportJob] in the format
     *           `projects/&#42;&#47;locations/&#42;&#47;keyRings/&#42;&#47;importJobs/&#42;`.
     *     @type int $import_method
     *           Required. Immutable. The wrapping method to be used for incoming key
     *           material.
     *     @type int $protection_level
     *           Required. Immutable. The protection level of the
     *           [ImportJob][google.cloud.kms.v1.ImportJob]. This must match the
     *           [protection_level][google.cloud.kms.v1.CryptoKeyVersionTemplate.protection_level]
     *           of the [version_template][google.cloud.kms.v1.CryptoKey.version_template]
     *           on the [CryptoKey][google.cloud.kms.v1.CryptoKey] you attempt to import
     *           into.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Output only. The time at which this
     *           [ImportJob][google.cloud.kms.v1.ImportJob] was created.
     *     @type \Google\Protobuf\Timestamp $generate_time
     *           Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]'s key
     *           material was generated.
     *     @type \Google\Protobuf\Timestamp $expire_time
     *           Output only. The time at which this
     *           [ImportJob][google.cloud.kms.v1.ImportJob] is scheduled for expiration and
     *           can no longer be used to import key material.
     *     @type \Google\Protobuf\Timestamp $expire_event_time
     *           Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]
     *           expired. Only present if [state][google.cloud.kms.v1.ImportJob.state] is
     *           [EXPIRED][google.cloud.kms.v1.ImportJob.ImportJobState.EXPIRED].
     *     @type int $state
     *           Output only. The current state of the
     *           [ImportJob][google.cloud.kms.v1.ImportJob], indicating if it can be used.
     *     @type \Google\Cloud\Kms\V1\ImportJob\WrappingPublicKey $public_key
     *           Output only. The public key with which to wrap key material prior to
     *           import. Only returned if [state][google.cloud.kms.v1.ImportJob.state] is
     *           [ACTIVE][google.cloud.kms.v1.ImportJob.ImportJobState.ACTIVE].
     *     @type \Google\Cloud\Kms\V1\KeyOperationAttestation $attestation
     *           Output only. Statement that was generated and signed by the key creator
     *           (for example, an HSM) at key creation time. Use this statement to verify
     *           attributes of the key as stored on the HSM, independently of Google.
     *           Only present if the chosen
     *           [ImportMethod][google.cloud.kms.v1.ImportJob.ImportMethod] is one with a
     *           protection level of [HSM][google.cloud.kms.v1.ProtectionLevel.HSM].
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Kms\V1\Resources::initOnce();
        parent::__construct($data);
    }

    /**
     * Output only. The resource name for this
     * [ImportJob][google.cloud.kms.v1.ImportJob] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;keyRings/&#42;&#47;importJobs/&#42;`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Output only. The resource name for this
     * [ImportJob][google.cloud.kms.v1.ImportJob] in the format
     * `projects/&#42;&#47;locations/&#42;&#47;keyRings/&#42;&#47;importJobs/&#42;`.
     *
     * Generated from protobuf field <code>string name = 1 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * Required. Immutable. The wrapping method to be used for incoming key
     * material.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportMethod import_method = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     * @return int
     */
    public function getImportMethod()
    {
        return $this->import_method;
    }

    /**
     * Required. Immutable. The wrapping method to be used for incoming key
     * material.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportMethod import_method = 2 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     * @param int $var
     * @return $this
     */
    public function setImportMethod($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Kms\V1\ImportJob\ImportMethod::class);
        $this->import_method = $var;

        return $this;
    }

    /**
     * Required. Immutable. The protection level of the
     * [ImportJob][google.cloud.kms.v1.ImportJob]. This must match the
     * [protection_level][google.cloud.kms.v1.CryptoKeyVersionTemplate.protection_level]
     * of the [version_template][google.cloud.kms.v1.CryptoKey.version_template]
     * on the [CryptoKey][google.cloud.kms.v1.CryptoKey] you attempt to import
     * into.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ProtectionLevel protection_level = 9 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     * @return int
     */
    public function getProtectionLevel()
    {
        return $this->protection_level;
    }

    /**
     * Required. Immutable. The protection level of the
     * [ImportJob][google.cloud.kms.v1.ImportJob]. This must match the
     * [protection_level][google.cloud.kms.v1.CryptoKeyVersionTemplate.protection_level]
     * of the [version_template][google.cloud.kms.v1.CryptoKey.version_template]
     * on the [CryptoKey][google.cloud.kms.v1.CryptoKey] you attempt to import
     * into.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ProtectionLevel protection_level = 9 [(.google.api.field_behavior) = REQUIRED, (.google.api.field_behavior) = IMMUTABLE];</code>
     * @param int $var
     * @return $this
     */
    public function setProtectionLevel($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Kms\V1\ProtectionLevel::class);
        $this->protection_level = $var;

        return $this;
    }

    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    public function hasCreateTime()
    {
        return isset($this->create_time);
    }

    public function clearCreateTime()
    {
        unset($this->create_time);
    }

    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] was created.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 3 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]'s key
     * material was generated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp generate_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getGenerateTime()
    {
        return $this->generate_time;
    }

    public function hasGenerateTime()
    {
        return isset($this->generate_time);
    }

    public function clearGenerateTime()
    {
        unset($this->generate_time);
    }

    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]'s key
     * material was generated.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp generate_time = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setGenerateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->generate_time = $var;

        return $this;
    }

    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] is scheduled for expiration and
     * can no longer be used to import key material.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getExpireTime()
    {
        return $this->expire_time;
    }

    public function hasExpireTime()
    {
        return isset($this->expire_time);
    }

    public function clearExpireTime()
    {
        unset($this->expire_time);
    }

    /**
     * Output only. The time at which this
     * [ImportJob][google.cloud.kms.v1.ImportJob] is scheduled for expiration and
     * can no longer be used to import key material.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_time = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setExpireTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->expire_time = $var;

        return $this;
    }

    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]
     * expired. Only present if [state][google.cloud.kms.v1.ImportJob.state] is
     * [EXPIRED][google.cloud.kms.v1.ImportJob.ImportJobState.EXPIRED].
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_event_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Timestamp|null
     */
    public function getExpireEventTime()
    {
        return $this->expire_event_time;
    }

    public function hasExpireEventTime()
    {
        return isset($this->expire_event_time);
    }

    public function clearExpireEventTime()
    {
        unset($this->expire_event_time);
    }

    /**
     * Output only. The time this [ImportJob][google.cloud.kms.v1.ImportJob]
     * expired. Only present if [state][google.cloud.kms.v1.ImportJob.state] is
     * [EXPIRED][google.cloud.kms.v1.ImportJob.ImportJobState.EXPIRED].
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp expire_event_time = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setExpireEventTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->expire_event_time = $var;

        return $this;
    }

    /**
     * Output only. The current state of the
     * [ImportJob][google.cloud.kms.v1.ImportJob], indicating if it can be used.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportJobState state = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Output only. The current state of the
     * [ImportJob][google.cloud.kms.v1.ImportJob], indicating if it can be used.
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.ImportJobState state = 6 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setState($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Kms\V1\ImportJob\ImportJobState::class);
        $this->state = $var;

        return $this;
    }

    /**
     * Output only. The public key with which to wrap key material prior to
     * import. Only returned if [state][google.cloud.kms.v1.ImportJob.state] is
     * [ACTIVE][google.cloud.kms.v1.ImportJob.ImportJobState.ACTIVE].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.WrappingPublicKey public_key = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\Kms\V1\ImportJob\WrappingPublicKey|null
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    public function hasPublicKey()
    {
        return isset($this->public_key);
    }

    public function clearPublicKey()
    {
        unset($this->public_key);
    }

    /**
     * Output only. The public key with which to wrap key material prior to
     * import. Only returned if [state][google.cloud.kms.v1.ImportJob.state] is
     * [ACTIVE][google.cloud.kms.v1.ImportJob.ImportJobState.ACTIVE].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.ImportJob.WrappingPublicKey public_key = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Kms\V1\ImportJob\WrappingPublicKey $var
     * @return $this
     */
    public function setPublicKey($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Kms\V1\ImportJob\WrappingPublicKey::class);
        $this->public_key = $var;

        return $this;
    }

    /**
     * Output only. Statement that was generated and signed by the key creator
     * (for example, an HSM) at key creation time. Use this statement to verify
     * attributes of the key as stored on the HSM, independently of Google.
     * Only present if the chosen
     * [ImportMethod][google.cloud.kms.v1.ImportJob.ImportMethod] is one with a
     * protection level of [HSM][google.cloud.kms.v1.ProtectionLevel.HSM].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.KeyOperationAttestation attestation = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Cloud\Kms\V1\KeyOperationAttestation|null
     */
    public function getAttestation()
    {
        return $this->attestation;
    }

    public function hasAttestation()
    {
        return isset($this->attestation);
    }

    public function clearAttestation()
    {
        unset($this->attestation);
    }

    /**
     * Output only. Statement that was generated and signed by the key creator
     * (for example, an HSM) at key creation time. Use this statement to verify
     * attributes of the key as stored on the HSM, independently of Google.
     * Only present if the chosen
     * [ImportMethod][google.cloud.kms.v1.ImportJob.ImportMethod] is one with a
     * protection level of [HSM][google.cloud.kms.v1.ProtectionLevel.HSM].
     *
     * Generated from protobuf field <code>.google.cloud.kms.v1.KeyOperationAttestation attestation = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Cloud\Kms\V1\KeyOperationAttestation $var
     * @return $this
     */
    public function setAttestation($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Kms\V1\KeyOperationAttestation::class);
        $this->attestation = $var;

        return $this;
    }

}

