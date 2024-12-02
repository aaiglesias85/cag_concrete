<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grafeas/v1/provenance.proto

namespace Grafeas\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A SourceContext is a reference to a tree of files. A SourceContext together
 * with a path point to a unique revision of a single file or directory.
 *
 * Generated from protobuf message <code>grafeas.v1.SourceContext</code>
 */
class SourceContext extends \Google\Protobuf\Internal\Message
{
    /**
     * Labels with user defined metadata.
     *
     * Generated from protobuf field <code>map<string, string> labels = 4;</code>
     */
    private $labels;
    protected $context;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Grafeas\V1\CloudRepoSourceContext $cloud_repo
     *           A SourceContext referring to a revision in a Google Cloud Source Repo.
     *     @type \Grafeas\V1\GerritSourceContext $gerrit
     *           A SourceContext referring to a Gerrit project.
     *     @type \Grafeas\V1\GitSourceContext $git
     *           A SourceContext referring to any third party Git repo (e.g., GitHub).
     *     @type array|\Google\Protobuf\Internal\MapField $labels
     *           Labels with user defined metadata.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Grafeas\V1\Provenance::initOnce();
        parent::__construct($data);
    }

    /**
     * A SourceContext referring to a revision in a Google Cloud Source Repo.
     *
     * Generated from protobuf field <code>.grafeas.v1.CloudRepoSourceContext cloud_repo = 1;</code>
     * @return \Grafeas\V1\CloudRepoSourceContext|null
     */
    public function getCloudRepo()
    {
        return $this->readOneof(1);
    }

    public function hasCloudRepo()
    {
        return $this->hasOneof(1);
    }

    /**
     * A SourceContext referring to a revision in a Google Cloud Source Repo.
     *
     * Generated from protobuf field <code>.grafeas.v1.CloudRepoSourceContext cloud_repo = 1;</code>
     * @param \Grafeas\V1\CloudRepoSourceContext $var
     * @return $this
     */
    public function setCloudRepo($var)
    {
        GPBUtil::checkMessage($var, \Grafeas\V1\CloudRepoSourceContext::class);
        $this->writeOneof(1, $var);

        return $this;
    }

    /**
     * A SourceContext referring to a Gerrit project.
     *
     * Generated from protobuf field <code>.grafeas.v1.GerritSourceContext gerrit = 2;</code>
     * @return \Grafeas\V1\GerritSourceContext|null
     */
    public function getGerrit()
    {
        return $this->readOneof(2);
    }

    public function hasGerrit()
    {
        return $this->hasOneof(2);
    }

    /**
     * A SourceContext referring to a Gerrit project.
     *
     * Generated from protobuf field <code>.grafeas.v1.GerritSourceContext gerrit = 2;</code>
     * @param \Grafeas\V1\GerritSourceContext $var
     * @return $this
     */
    public function setGerrit($var)
    {
        GPBUtil::checkMessage($var, \Grafeas\V1\GerritSourceContext::class);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * A SourceContext referring to any third party Git repo (e.g., GitHub).
     *
     * Generated from protobuf field <code>.grafeas.v1.GitSourceContext git = 3;</code>
     * @return \Grafeas\V1\GitSourceContext|null
     */
    public function getGit()
    {
        return $this->readOneof(3);
    }

    public function hasGit()
    {
        return $this->hasOneof(3);
    }

    /**
     * A SourceContext referring to any third party Git repo (e.g., GitHub).
     *
     * Generated from protobuf field <code>.grafeas.v1.GitSourceContext git = 3;</code>
     * @param \Grafeas\V1\GitSourceContext $var
     * @return $this
     */
    public function setGit($var)
    {
        GPBUtil::checkMessage($var, \Grafeas\V1\GitSourceContext::class);
        $this->writeOneof(3, $var);

        return $this;
    }

    /**
     * Labels with user defined metadata.
     *
     * Generated from protobuf field <code>map<string, string> labels = 4;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Labels with user defined metadata.
     *
     * Generated from protobuf field <code>map<string, string> labels = 4;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setLabels($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->labels = $arr;

        return $this;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->whichOneof("context");
    }

}

