<?php
/*
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * GENERATED CODE WARNING
 * Generated by gapic-generator-php from the file
 * https://github.com/googleapis/googleapis/blob/master/google/cloud/dataplex/v1/metadata.proto
 * Updates to the above are reflected here through a refresh process.
 */

namespace Google\Cloud\Dataplex\V1\Gapic;

use Google\ApiCore\ApiException;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\GapicClientTrait;

use Google\ApiCore\PathTemplate;
use Google\ApiCore\RequestParamsHeaderDescriptor;
use Google\ApiCore\RetrySettings;
use Google\ApiCore\Transport\TransportInterface;
use Google\ApiCore\ValidationException;
use Google\Auth\FetchAuthTokenInterface;
use Google\Cloud\Dataplex\V1\CreateEntityRequest;
use Google\Cloud\Dataplex\V1\CreatePartitionRequest;
use Google\Cloud\Dataplex\V1\DeleteEntityRequest;
use Google\Cloud\Dataplex\V1\DeletePartitionRequest;
use Google\Cloud\Dataplex\V1\Entity;
use Google\Cloud\Dataplex\V1\GetEntityRequest;
use Google\Cloud\Dataplex\V1\GetEntityRequest\EntityView;
use Google\Cloud\Dataplex\V1\GetPartitionRequest;
use Google\Cloud\Dataplex\V1\ListEntitiesRequest;
use Google\Cloud\Dataplex\V1\ListEntitiesResponse;
use Google\Cloud\Dataplex\V1\ListPartitionsRequest;
use Google\Cloud\Dataplex\V1\ListPartitionsResponse;
use Google\Cloud\Dataplex\V1\Partition;
use Google\Cloud\Dataplex\V1\UpdateEntityRequest;
use Google\Protobuf\GPBEmpty;

/**
 * Service Description: Metadata service manages metadata resources such as tables, filesets and
 * partitions.
 *
 * This class provides the ability to make remote calls to the backing service through method
 * calls that map to API methods. Sample code to get started:
 *
 * ```
 * $metadataServiceClient = new MetadataServiceClient();
 * try {
 *     $formattedParent = $metadataServiceClient->zoneName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]');
 *     $entity = new Entity();
 *     $response = $metadataServiceClient->createEntity($formattedParent, $entity);
 * } finally {
 *     $metadataServiceClient->close();
 * }
 * ```
 *
 * Many parameters require resource names to be formatted in a particular way. To
 * assist with these names, this class includes a format method for each type of
 * name, and additionally a parseName method to extract the individual identifiers
 * contained within formatted names that are returned by the API.
 */
class MetadataServiceGapicClient
{
    use GapicClientTrait;

    /**
     * The name of the service.
     */
    const SERVICE_NAME = 'google.cloud.dataplex.v1.MetadataService';

    /**
     * The default address of the service.
     */
    const SERVICE_ADDRESS = 'dataplex.googleapis.com';

    /**
     * The default port of the service.
     */
    const DEFAULT_SERVICE_PORT = 443;

    /**
     * The name of the code generator, to be included in the agent header.
     */
    const CODEGEN_NAME = 'gapic';

    /**
     * The default scopes required by the service.
     */
    public static $serviceScopes = [
        'https://www.googleapis.com/auth/cloud-platform',
    ];

    private static $entityNameTemplate;

    private static $partitionNameTemplate;

    private static $zoneNameTemplate;

    private static $pathTemplateMap;

    private static function getClientDefaults()
    {
        return [
            'serviceName' => self::SERVICE_NAME,
            'apiEndpoint' =>
                self::SERVICE_ADDRESS . ':' . self::DEFAULT_SERVICE_PORT,
            'clientConfig' =>
                __DIR__ . '/../resources/metadata_service_client_config.json',
            'descriptorsConfigPath' =>
                __DIR__ .
                '/../resources/metadata_service_descriptor_config.php',
            'gcpApiConfigPath' =>
                __DIR__ . '/../resources/metadata_service_grpc_config.json',
            'credentialsConfig' => [
                'defaultScopes' => self::$serviceScopes,
            ],
            'transportConfig' => [
                'rest' => [
                    'restClientConfigPath' =>
                        __DIR__ .
                        '/../resources/metadata_service_rest_client_config.php',
                ],
            ],
        ];
    }

    private static function getEntityNameTemplate()
    {
        if (self::$entityNameTemplate == null) {
            self::$entityNameTemplate = new PathTemplate(
                'projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}/entities/{entity}'
            );
        }

        return self::$entityNameTemplate;
    }

    private static function getPartitionNameTemplate()
    {
        if (self::$partitionNameTemplate == null) {
            self::$partitionNameTemplate = new PathTemplate(
                'projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}/entities/{entity}/partitions/{partition}'
            );
        }

        return self::$partitionNameTemplate;
    }

    private static function getZoneNameTemplate()
    {
        if (self::$zoneNameTemplate == null) {
            self::$zoneNameTemplate = new PathTemplate(
                'projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}'
            );
        }

        return self::$zoneNameTemplate;
    }

    private static function getPathTemplateMap()
    {
        if (self::$pathTemplateMap == null) {
            self::$pathTemplateMap = [
                'entity' => self::getEntityNameTemplate(),
                'partition' => self::getPartitionNameTemplate(),
                'zone' => self::getZoneNameTemplate(),
            ];
        }

        return self::$pathTemplateMap;
    }

    /**
     * Formats a string containing the fully-qualified path to represent a entity
     * resource.
     *
     * @param string $project
     * @param string $location
     * @param string $lake
     * @param string $zone
     * @param string $entity
     *
     * @return string The formatted entity resource.
     */
    public static function entityName(
        $project,
        $location,
        $lake,
        $zone,
        $entity
    ) {
        return self::getEntityNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'lake' => $lake,
            'zone' => $zone,
            'entity' => $entity,
        ]);
    }

    /**
     * Formats a string containing the fully-qualified path to represent a partition
     * resource.
     *
     * @param string $project
     * @param string $location
     * @param string $lake
     * @param string $zone
     * @param string $entity
     * @param string $partition
     *
     * @return string The formatted partition resource.
     */
    public static function partitionName(
        $project,
        $location,
        $lake,
        $zone,
        $entity,
        $partition
    ) {
        return self::getPartitionNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'lake' => $lake,
            'zone' => $zone,
            'entity' => $entity,
            'partition' => $partition,
        ]);
    }

    /**
     * Formats a string containing the fully-qualified path to represent a zone
     * resource.
     *
     * @param string $project
     * @param string $location
     * @param string $lake
     * @param string $zone
     *
     * @return string The formatted zone resource.
     */
    public static function zoneName($project, $location, $lake, $zone)
    {
        return self::getZoneNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'lake' => $lake,
            'zone' => $zone,
        ]);
    }

    /**
     * Parses a formatted name string and returns an associative array of the components in the name.
     * The following name formats are supported:
     * Template: Pattern
     * - entity: projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}/entities/{entity}
     * - partition: projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}/entities/{entity}/partitions/{partition}
     * - zone: projects/{project}/locations/{location}/lakes/{lake}/zones/{zone}
     *
     * The optional $template argument can be supplied to specify a particular pattern,
     * and must match one of the templates listed above. If no $template argument is
     * provided, or if the $template argument does not match one of the templates
     * listed, then parseName will check each of the supported templates, and return
     * the first match.
     *
     * @param string $formattedName The formatted name string
     * @param string $template      Optional name of template to match
     *
     * @return array An associative array from name component IDs to component values.
     *
     * @throws ValidationException If $formattedName could not be matched.
     */
    public static function parseName($formattedName, $template = null)
    {
        $templateMap = self::getPathTemplateMap();
        if ($template) {
            if (!isset($templateMap[$template])) {
                throw new ValidationException(
                    "Template name $template does not exist"
                );
            }

            return $templateMap[$template]->match($formattedName);
        }

        foreach ($templateMap as $templateName => $pathTemplate) {
            try {
                return $pathTemplate->match($formattedName);
            } catch (ValidationException $ex) {
                // Swallow the exception to continue trying other path templates
            }
        }

        throw new ValidationException(
            "Input did not match any known format. Input: $formattedName"
        );
    }

    /**
     * Constructor.
     *
     * @param array $options {
     *     Optional. Options for configuring the service API wrapper.
     *
     *     @type string $serviceAddress
     *           **Deprecated**. This option will be removed in a future major release. Please
     *           utilize the `$apiEndpoint` option instead.
     *     @type string $apiEndpoint
     *           The address of the API remote host. May optionally include the port, formatted
     *           as "<uri>:<port>". Default 'dataplex.googleapis.com:443'.
     *     @type string|array|FetchAuthTokenInterface|CredentialsWrapper $credentials
     *           The credentials to be used by the client to authorize API calls. This option
     *           accepts either a path to a credentials file, or a decoded credentials file as a
     *           PHP array.
     *           *Advanced usage*: In addition, this option can also accept a pre-constructed
     *           {@see \Google\Auth\FetchAuthTokenInterface} object or
     *           {@see \Google\ApiCore\CredentialsWrapper} object. Note that when one of these
     *           objects are provided, any settings in $credentialsConfig will be ignored.
     *     @type array $credentialsConfig
     *           Options used to configure credentials, including auth token caching, for the
     *           client. For a full list of supporting configuration options, see
     *           {@see \Google\ApiCore\CredentialsWrapper::build()} .
     *     @type bool $disableRetries
     *           Determines whether or not retries defined by the client configuration should be
     *           disabled. Defaults to `false`.
     *     @type string|array $clientConfig
     *           Client method configuration, including retry settings. This option can be either
     *           a path to a JSON file, or a PHP array containing the decoded JSON data. By
     *           default this settings points to the default client config file, which is
     *           provided in the resources folder.
     *     @type string|TransportInterface $transport
     *           The transport used for executing network requests. May be either the string
     *           `rest` or `grpc`. Defaults to `grpc` if gRPC support is detected on the system.
     *           *Advanced usage*: Additionally, it is possible to pass in an already
     *           instantiated {@see \Google\ApiCore\Transport\TransportInterface} object. Note
     *           that when this object is provided, any settings in $transportConfig, and any
     *           $serviceAddress setting, will be ignored.
     *     @type array $transportConfig
     *           Configuration options that will be used to construct the transport. Options for
     *           each supported transport type should be passed in a key for that transport. For
     *           example:
     *           $transportConfig = [
     *               'grpc' => [...],
     *               'rest' => [...],
     *           ];
     *           See the {@see \Google\ApiCore\Transport\GrpcTransport::build()} and
     *           {@see \Google\ApiCore\Transport\RestTransport::build()} methods for the
     *           supported options.
     *     @type callable $clientCertSource
     *           A callable which returns the client cert as a string. This can be used to
     *           provide a certificate and private key to the transport layer for mTLS.
     * }
     *
     * @throws ValidationException
     */
    public function __construct(array $options = [])
    {
        $clientOptions = $this->buildClientOptions($options);
        $this->setClientOptions($clientOptions);
    }

    /**
     * Create a metadata entity.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedParent = $metadataServiceClient->zoneName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]');
     *     $entity = new Entity();
     *     $response = $metadataServiceClient->createEntity($formattedParent, $entity);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $parent       Required. The resource name of the parent zone:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}`.
     * @param Entity $entity       Required. Entity resource.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type bool $validateOnly
     *           Optional. Only validate the request, but do not perform mutations.
     *           The default is false.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Dataplex\V1\Entity
     *
     * @throws ApiException if the remote call fails
     */
    public function createEntity($parent, $entity, array $optionalArgs = [])
    {
        $request = new CreateEntityRequest();
        $requestParamHeaders = [];
        $request->setParent($parent);
        $request->setEntity($entity);
        $requestParamHeaders['parent'] = $parent;
        if (isset($optionalArgs['validateOnly'])) {
            $request->setValidateOnly($optionalArgs['validateOnly']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'CreateEntity',
            Entity::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * Create a metadata partition.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedParent = $metadataServiceClient->entityName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]');
     *     $partition = new Partition();
     *     $response = $metadataServiceClient->createPartition($formattedParent, $partition);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string    $parent       Required. The resource name of the parent zone:
     *                                `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}`.
     * @param Partition $partition    Required. Partition resource.
     * @param array     $optionalArgs {
     *     Optional.
     *
     *     @type bool $validateOnly
     *           Optional. Only validate the request, but do not perform mutations.
     *           The default is false.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Dataplex\V1\Partition
     *
     * @throws ApiException if the remote call fails
     */
    public function createPartition(
        $parent,
        $partition,
        array $optionalArgs = []
    ) {
        $request = new CreatePartitionRequest();
        $requestParamHeaders = [];
        $request->setParent($parent);
        $request->setPartition($partition);
        $requestParamHeaders['parent'] = $parent;
        if (isset($optionalArgs['validateOnly'])) {
            $request->setValidateOnly($optionalArgs['validateOnly']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'CreatePartition',
            Partition::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * Delete a metadata entity.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedName = $metadataServiceClient->entityName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]');
     *     $etag = 'etag';
     *     $metadataServiceClient->deleteEntity($formattedName, $etag);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $name         Required. The resource name of the entity:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}`.
     * @param string $etag         Required. The etag associated with the partition if it was previously retrieved.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @throws ApiException if the remote call fails
     */
    public function deleteEntity($name, $etag, array $optionalArgs = [])
    {
        $request = new DeleteEntityRequest();
        $requestParamHeaders = [];
        $request->setName($name);
        $request->setEtag($etag);
        $requestParamHeaders['name'] = $name;
        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'DeleteEntity',
            GPBEmpty::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * Delete a metadata partition.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedName = $metadataServiceClient->partitionName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]', '[PARTITION]');
     *     $metadataServiceClient->deletePartition($formattedName);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $name         Required. The resource name of the partition.
     *                             format:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}/partitions/{partition_value_path}`.
     *                             The {partition_value_path} segment consists of an ordered sequence of
     *                             partition values separated by "/". All values must be provided.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type string $etag
     *           Optional. The etag associated with the partition if it was previously retrieved.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @throws ApiException if the remote call fails
     */
    public function deletePartition($name, array $optionalArgs = [])
    {
        $request = new DeletePartitionRequest();
        $requestParamHeaders = [];
        $request->setName($name);
        $requestParamHeaders['name'] = $name;
        if (isset($optionalArgs['etag'])) {
            $request->setEtag($optionalArgs['etag']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'DeletePartition',
            GPBEmpty::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * Get a metadata entity.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedName = $metadataServiceClient->entityName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]');
     *     $response = $metadataServiceClient->getEntity($formattedName);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $name         Required. The resource name of the entity:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}.`
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type int $view
     *           Optional. Used to select the subset of entity information to return.
     *           Defaults to `BASIC`.
     *           For allowed values, use constants defined on {@see \Google\Cloud\Dataplex\V1\GetEntityRequest\EntityView}
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Dataplex\V1\Entity
     *
     * @throws ApiException if the remote call fails
     */
    public function getEntity($name, array $optionalArgs = [])
    {
        $request = new GetEntityRequest();
        $requestParamHeaders = [];
        $request->setName($name);
        $requestParamHeaders['name'] = $name;
        if (isset($optionalArgs['view'])) {
            $request->setView($optionalArgs['view']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'GetEntity',
            Entity::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * Get a metadata partition of an entity.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedName = $metadataServiceClient->partitionName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]', '[PARTITION]');
     *     $response = $metadataServiceClient->getPartition($formattedName);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $name         Required. The resource name of the partition:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}/partitions/{partition_value_path}`.
     *                             The {partition_value_path} segment consists of an ordered sequence of
     *                             partition values separated by "/". All values must be provided.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Dataplex\V1\Partition
     *
     * @throws ApiException if the remote call fails
     */
    public function getPartition($name, array $optionalArgs = [])
    {
        $request = new GetPartitionRequest();
        $requestParamHeaders = [];
        $request->setName($name);
        $requestParamHeaders['name'] = $name;
        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'GetPartition',
            Partition::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * List metadata entities in a zone.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedParent = $metadataServiceClient->zoneName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]');
     *     $view = EntityView::ENTITY_VIEW_UNSPECIFIED;
     *     // Iterate over pages of elements
     *     $pagedResponse = $metadataServiceClient->listEntities($formattedParent, $view);
     *     foreach ($pagedResponse->iteratePages() as $page) {
     *         foreach ($page as $element) {
     *             // doSomethingWith($element);
     *         }
     *     }
     *     // Alternatively:
     *     // Iterate through all elements
     *     $pagedResponse = $metadataServiceClient->listEntities($formattedParent, $view);
     *     foreach ($pagedResponse->iterateAllElements() as $element) {
     *         // doSomethingWith($element);
     *     }
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $parent       Required. The resource name of the parent zone:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}`.
     * @param int    $view         Required. Specify the entity view to make a partial list request.
     *                             For allowed values, use constants defined on {@see \Google\Cloud\Dataplex\V1\ListEntitiesRequest\EntityView}
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type int $pageSize
     *           The maximum number of resources contained in the underlying API
     *           response. The API may return fewer values in a page, even if
     *           there are additional values to be retrieved.
     *     @type string $pageToken
     *           A page token is used to specify a page of values to be returned.
     *           If no page token is specified (the default), the first page
     *           of values will be returned. Any page token used here must have
     *           been generated by a previous call to the API.
     *     @type string $filter
     *           Optional. The following filter parameters can be added to the URL to limit the
     *           entities returned by the API:
     *
     *           - Entity ID: ?filter="id=entityID"
     *           - Asset ID: ?filter="asset=assetID"
     *           - Data path ?filter="data_path=gs://my-bucket"
     *           - Is HIVE compatible: ?filter=”hive_compatible=true”
     *           - Is BigQuery compatible: ?filter=”bigquery_compatible=true”
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\ApiCore\PagedListResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function listEntities($parent, $view, array $optionalArgs = [])
    {
        $request = new ListEntitiesRequest();
        $requestParamHeaders = [];
        $request->setParent($parent);
        $request->setView($view);
        $requestParamHeaders['parent'] = $parent;
        if (isset($optionalArgs['pageSize'])) {
            $request->setPageSize($optionalArgs['pageSize']);
        }

        if (isset($optionalArgs['pageToken'])) {
            $request->setPageToken($optionalArgs['pageToken']);
        }

        if (isset($optionalArgs['filter'])) {
            $request->setFilter($optionalArgs['filter']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->getPagedListResponse(
            'ListEntities',
            $optionalArgs,
            ListEntitiesResponse::class,
            $request
        );
    }

    /**
     * List metadata partitions of an entity.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $formattedParent = $metadataServiceClient->entityName('[PROJECT]', '[LOCATION]', '[LAKE]', '[ZONE]', '[ENTITY]');
     *     // Iterate over pages of elements
     *     $pagedResponse = $metadataServiceClient->listPartitions($formattedParent);
     *     foreach ($pagedResponse->iteratePages() as $page) {
     *         foreach ($page as $element) {
     *             // doSomethingWith($element);
     *         }
     *     }
     *     // Alternatively:
     *     // Iterate through all elements
     *     $pagedResponse = $metadataServiceClient->listPartitions($formattedParent);
     *     foreach ($pagedResponse->iterateAllElements() as $element) {
     *         // doSomethingWith($element);
     *     }
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param string $parent       Required. The resource name of the parent entity:
     *                             `projects/{project_number}/locations/{location_id}/lakes/{lake_id}/zones/{zone_id}/entities/{entity_id}`.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type int $pageSize
     *           The maximum number of resources contained in the underlying API
     *           response. The API may return fewer values in a page, even if
     *           there are additional values to be retrieved.
     *     @type string $pageToken
     *           A page token is used to specify a page of values to be returned.
     *           If no page token is specified (the default), the first page
     *           of values will be returned. Any page token used here must have
     *           been generated by a previous call to the API.
     *     @type string $filter
     *           Optional. Filter the partitions returned to the caller using a key vslue pair
     *           expression. The filter expression supports:
     *
     *           - logical operators: AND, OR
     *           - comparison operators: <, >, >=, <= ,=, !=
     *           - LIKE operators:
     *           - The right hand of a LIKE operator supports “.” and
     *           “*” for wildcard searches, for example "value1 LIKE ".*oo.*"
     *           - parenthetical grouping: ( )
     *
     *           Sample filter expression: `?filter="key1 < value1 OR key2 > value2"
     *
     *           **Notes:**
     *
     *           - Keys to the left of operators are case insensitive.
     *           - Partition results are sorted first by creation time, then by
     *           lexicographic order.
     *           - Up to 20 key value filter pairs are allowed, but due to performance
     *           considerations, only the first 10 will be used as a filter.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\ApiCore\PagedListResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function listPartitions($parent, array $optionalArgs = [])
    {
        $request = new ListPartitionsRequest();
        $requestParamHeaders = [];
        $request->setParent($parent);
        $requestParamHeaders['parent'] = $parent;
        if (isset($optionalArgs['pageSize'])) {
            $request->setPageSize($optionalArgs['pageSize']);
        }

        if (isset($optionalArgs['pageToken'])) {
            $request->setPageToken($optionalArgs['pageToken']);
        }

        if (isset($optionalArgs['filter'])) {
            $request->setFilter($optionalArgs['filter']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->getPagedListResponse(
            'ListPartitions',
            $optionalArgs,
            ListPartitionsResponse::class,
            $request
        );
    }

    /**
     * Update a metadata entity. Only supports full resource update.
     *
     * Sample code:
     * ```
     * $metadataServiceClient = new MetadataServiceClient();
     * try {
     *     $entity = new Entity();
     *     $response = $metadataServiceClient->updateEntity($entity);
     * } finally {
     *     $metadataServiceClient->close();
     * }
     * ```
     *
     * @param Entity $entity       Required. Update description.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type bool $validateOnly
     *           Optional. Only validate the request, but do not perform mutations.
     *           The default is false.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a
     *           {@see Google\ApiCore\RetrySettings} object, or an associative array of retry
     *           settings parameters. See the documentation on
     *           {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Dataplex\V1\Entity
     *
     * @throws ApiException if the remote call fails
     */
    public function updateEntity($entity, array $optionalArgs = [])
    {
        $request = new UpdateEntityRequest();
        $requestParamHeaders = [];
        $request->setEntity($entity);
        $requestParamHeaders['entity.name'] = $entity->getName();
        if (isset($optionalArgs['validateOnly'])) {
            $request->setValidateOnly($optionalArgs['validateOnly']);
        }

        $requestParams = new RequestParamsHeaderDescriptor(
            $requestParamHeaders
        );
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();
        return $this->startCall(
            'UpdateEntity',
            Entity::class,
            $optionalArgs,
            $request
        )->wait();
    }
}
