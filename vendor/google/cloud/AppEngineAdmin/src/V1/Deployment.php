<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/appengine/v1/deploy.proto

namespace Google\Cloud\AppEngine\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Code and application artifacts used to deploy a version to App Engine.
 *
 * Generated from protobuf message <code>google.appengine.v1.Deployment</code>
 */
class Deployment extends \Google\Protobuf\Internal\Message
{
    /**
     * Manifest of the files stored in Google Cloud Storage that are included
     * as part of this version. All files must be readable using the
     * credentials supplied with this call.
     *
     * Generated from protobuf field <code>map<string, .google.appengine.v1.FileInfo> files = 1;</code>
     */
    private $files;
    /**
     * The Docker image for the container that runs the version.
     * Only applicable for instances running in the App Engine flexible environment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ContainerInfo container = 2;</code>
     */
    private $container = null;
    /**
     * The zip file for this deployment, if this is a zip deployment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ZipInfo zip = 3;</code>
     */
    private $zip = null;
    /**
     * Options for any Google Cloud Build builds created as a part of this
     * deployment.
     * These options will only be used if a new build is created, such as when
     * deploying to the App Engine flexible environment using files or zip.
     *
     * Generated from protobuf field <code>.google.appengine.v1.CloudBuildOptions cloud_build_options = 6;</code>
     */
    private $cloud_build_options = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array|\Google\Protobuf\Internal\MapField $files
     *           Manifest of the files stored in Google Cloud Storage that are included
     *           as part of this version. All files must be readable using the
     *           credentials supplied with this call.
     *     @type \Google\Cloud\AppEngine\V1\ContainerInfo $container
     *           The Docker image for the container that runs the version.
     *           Only applicable for instances running in the App Engine flexible environment.
     *     @type \Google\Cloud\AppEngine\V1\ZipInfo $zip
     *           The zip file for this deployment, if this is a zip deployment.
     *     @type \Google\Cloud\AppEngine\V1\CloudBuildOptions $cloud_build_options
     *           Options for any Google Cloud Build builds created as a part of this
     *           deployment.
     *           These options will only be used if a new build is created, such as when
     *           deploying to the App Engine flexible environment using files or zip.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Appengine\V1\Deploy::initOnce();
        parent::__construct($data);
    }

    /**
     * Manifest of the files stored in Google Cloud Storage that are included
     * as part of this version. All files must be readable using the
     * credentials supplied with this call.
     *
     * Generated from protobuf field <code>map<string, .google.appengine.v1.FileInfo> files = 1;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Manifest of the files stored in Google Cloud Storage that are included
     * as part of this version. All files must be readable using the
     * credentials supplied with this call.
     *
     * Generated from protobuf field <code>map<string, .google.appengine.v1.FileInfo> files = 1;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setFiles($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\AppEngine\V1\FileInfo::class);
        $this->files = $arr;

        return $this;
    }

    /**
     * The Docker image for the container that runs the version.
     * Only applicable for instances running in the App Engine flexible environment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ContainerInfo container = 2;</code>
     * @return \Google\Cloud\AppEngine\V1\ContainerInfo|null
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function hasContainer()
    {
        return isset($this->container);
    }

    public function clearContainer()
    {
        unset($this->container);
    }

    /**
     * The Docker image for the container that runs the version.
     * Only applicable for instances running in the App Engine flexible environment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ContainerInfo container = 2;</code>
     * @param \Google\Cloud\AppEngine\V1\ContainerInfo $var
     * @return $this
     */
    public function setContainer($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AppEngine\V1\ContainerInfo::class);
        $this->container = $var;

        return $this;
    }

    /**
     * The zip file for this deployment, if this is a zip deployment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ZipInfo zip = 3;</code>
     * @return \Google\Cloud\AppEngine\V1\ZipInfo|null
     */
    public function getZip()
    {
        return $this->zip;
    }

    public function hasZip()
    {
        return isset($this->zip);
    }

    public function clearZip()
    {
        unset($this->zip);
    }

    /**
     * The zip file for this deployment, if this is a zip deployment.
     *
     * Generated from protobuf field <code>.google.appengine.v1.ZipInfo zip = 3;</code>
     * @param \Google\Cloud\AppEngine\V1\ZipInfo $var
     * @return $this
     */
    public function setZip($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AppEngine\V1\ZipInfo::class);
        $this->zip = $var;

        return $this;
    }

    /**
     * Options for any Google Cloud Build builds created as a part of this
     * deployment.
     * These options will only be used if a new build is created, such as when
     * deploying to the App Engine flexible environment using files or zip.
     *
     * Generated from protobuf field <code>.google.appengine.v1.CloudBuildOptions cloud_build_options = 6;</code>
     * @return \Google\Cloud\AppEngine\V1\CloudBuildOptions|null
     */
    public function getCloudBuildOptions()
    {
        return $this->cloud_build_options;
    }

    public function hasCloudBuildOptions()
    {
        return isset($this->cloud_build_options);
    }

    public function clearCloudBuildOptions()
    {
        unset($this->cloud_build_options);
    }

    /**
     * Options for any Google Cloud Build builds created as a part of this
     * deployment.
     * These options will only be used if a new build is created, such as when
     * deploying to the App Engine flexible environment using files or zip.
     *
     * Generated from protobuf field <code>.google.appengine.v1.CloudBuildOptions cloud_build_options = 6;</code>
     * @param \Google\Cloud\AppEngine\V1\CloudBuildOptions $var
     * @return $this
     */
    public function setCloudBuildOptions($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\AppEngine\V1\CloudBuildOptions::class);
        $this->cloud_build_options = $var;

        return $this;
    }

}

