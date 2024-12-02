<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/spanner/admin/instance/v1/spanner_instance_admin.proto

namespace Google\Cloud\Spanner\Admin\Instance\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The request for [ListInstances][google.spanner.admin.instance.v1.InstanceAdmin.ListInstances].
 *
 * Generated from protobuf message <code>google.spanner.admin.instance.v1.ListInstancesRequest</code>
 */
class ListInstancesRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The name of the project for which a list of instances is
     * requested. Values are of the form `projects/<project>`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Number of instances to be returned in the response. If 0 or less, defaults
     * to the server's maximum allowed page size.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     */
    private $page_size = 0;
    /**
     * If non-empty, `page_token` should contain a
     * [next_page_token][google.spanner.admin.instance.v1.ListInstancesResponse.next_page_token] from a
     * previous [ListInstancesResponse][google.spanner.admin.instance.v1.ListInstancesResponse].
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     */
    private $page_token = '';
    /**
     * An expression for filtering the results of the request. Filter rules are
     * case insensitive. The fields eligible for filtering are:
     *   * `name`
     *   * `display_name`
     *   * `labels.key` where key is the name of a label
     * Some examples of using filters are:
     *   * `name:*` --> The instance has a name.
     *   * `name:Howl` --> The instance's name contains the string "howl".
     *   * `name:HOWL` --> Equivalent to above.
     *   * `NAME:howl` --> Equivalent to above.
     *   * `labels.env:*` --> The instance has the label "env".
     *   * `labels.env:dev` --> The instance has the label "env" and the value of
     *                        the label contains the string "dev".
     *   * `name:howl labels.env:dev` --> The instance's name contains "howl" and
     *                                  it has the label "env" with its value
     *                                  containing "dev".
     *
     * Generated from protobuf field <code>string filter = 4;</code>
     */
    private $filter = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The name of the project for which a list of instances is
     *           requested. Values are of the form `projects/<project>`.
     *     @type int $page_size
     *           Number of instances to be returned in the response. If 0 or less, defaults
     *           to the server's maximum allowed page size.
     *     @type string $page_token
     *           If non-empty, `page_token` should contain a
     *           [next_page_token][google.spanner.admin.instance.v1.ListInstancesResponse.next_page_token] from a
     *           previous [ListInstancesResponse][google.spanner.admin.instance.v1.ListInstancesResponse].
     *     @type string $filter
     *           An expression for filtering the results of the request. Filter rules are
     *           case insensitive. The fields eligible for filtering are:
     *             * `name`
     *             * `display_name`
     *             * `labels.key` where key is the name of a label
     *           Some examples of using filters are:
     *             * `name:*` --> The instance has a name.
     *             * `name:Howl` --> The instance's name contains the string "howl".
     *             * `name:HOWL` --> Equivalent to above.
     *             * `NAME:howl` --> Equivalent to above.
     *             * `labels.env:*` --> The instance has the label "env".
     *             * `labels.env:dev` --> The instance has the label "env" and the value of
     *                                  the label contains the string "dev".
     *             * `name:howl labels.env:dev` --> The instance's name contains "howl" and
     *                                            it has the label "env" with its value
     *                                            containing "dev".
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Spanner\Admin\Instance\V1\SpannerInstanceAdmin::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The name of the project for which a list of instances is
     * requested. Values are of the form `projects/<project>`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The name of the project for which a list of instances is
     * requested. Values are of the form `projects/<project>`.
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
     * Number of instances to be returned in the response. If 0 or less, defaults
     * to the server's maximum allowed page size.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @return int
     */
    public function getPageSize()
    {
        return $this->page_size;
    }

    /**
     * Number of instances to be returned in the response. If 0 or less, defaults
     * to the server's maximum allowed page size.
     *
     * Generated from protobuf field <code>int32 page_size = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setPageSize($var)
    {
        GPBUtil::checkInt32($var);
        $this->page_size = $var;

        return $this;
    }

    /**
     * If non-empty, `page_token` should contain a
     * [next_page_token][google.spanner.admin.instance.v1.ListInstancesResponse.next_page_token] from a
     * previous [ListInstancesResponse][google.spanner.admin.instance.v1.ListInstancesResponse].
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @return string
     */
    public function getPageToken()
    {
        return $this->page_token;
    }

    /**
     * If non-empty, `page_token` should contain a
     * [next_page_token][google.spanner.admin.instance.v1.ListInstancesResponse.next_page_token] from a
     * previous [ListInstancesResponse][google.spanner.admin.instance.v1.ListInstancesResponse].
     *
     * Generated from protobuf field <code>string page_token = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->page_token = $var;

        return $this;
    }

    /**
     * An expression for filtering the results of the request. Filter rules are
     * case insensitive. The fields eligible for filtering are:
     *   * `name`
     *   * `display_name`
     *   * `labels.key` where key is the name of a label
     * Some examples of using filters are:
     *   * `name:*` --> The instance has a name.
     *   * `name:Howl` --> The instance's name contains the string "howl".
     *   * `name:HOWL` --> Equivalent to above.
     *   * `NAME:howl` --> Equivalent to above.
     *   * `labels.env:*` --> The instance has the label "env".
     *   * `labels.env:dev` --> The instance has the label "env" and the value of
     *                        the label contains the string "dev".
     *   * `name:howl labels.env:dev` --> The instance's name contains "howl" and
     *                                  it has the label "env" with its value
     *                                  containing "dev".
     *
     * Generated from protobuf field <code>string filter = 4;</code>
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * An expression for filtering the results of the request. Filter rules are
     * case insensitive. The fields eligible for filtering are:
     *   * `name`
     *   * `display_name`
     *   * `labels.key` where key is the name of a label
     * Some examples of using filters are:
     *   * `name:*` --> The instance has a name.
     *   * `name:Howl` --> The instance's name contains the string "howl".
     *   * `name:HOWL` --> Equivalent to above.
     *   * `NAME:howl` --> Equivalent to above.
     *   * `labels.env:*` --> The instance has the label "env".
     *   * `labels.env:dev` --> The instance has the label "env" and the value of
     *                        the label contains the string "dev".
     *   * `name:howl labels.env:dev` --> The instance's name contains "howl" and
     *                                  it has the label "env" with its value
     *                                  containing "dev".
     *
     * Generated from protobuf field <code>string filter = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setFilter($var)
    {
        GPBUtil::checkString($var, True);
        $this->filter = $var;

        return $this;
    }

}

