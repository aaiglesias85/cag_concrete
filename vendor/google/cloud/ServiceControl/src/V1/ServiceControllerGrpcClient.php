<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2021 Google LLC
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
namespace Google\Cloud\ServiceControl\V1;

/**
 * [Google Service Control API](https://cloud.google.com/service-control/overview)
 *
 * Lets clients check and report operations against a [managed
 * service](https://cloud.google.com/service-management/reference/rpc/google.api/servicemanagement.v1#google.api.servicemanagement.v1.ManagedService).
 */
class ServiceControllerGrpcClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Checks whether an operation on a service should be allowed to proceed
     * based on the configuration of the service and related policies. It must be
     * called before the operation is executed.
     *
     * If feasible, the client should cache the check results and reuse them for
     * 60 seconds. In case of any server errors, the client should rely on the
     * cached results for much longer time to avoid outage.
     * WARNING: There is general 60s delay for the configuration and policy
     * propagation, therefore callers MUST NOT depend on the `Check` method having
     * the latest policy information.
     *
     * NOTE: the [CheckRequest][google.api.servicecontrol.v1.CheckRequest] has
     * the size limit (wire-format byte size) of 1MB.
     *
     * This method requires the `servicemanagement.services.check` permission
     * on the specified service. For more information, see
     * [Cloud IAM](https://cloud.google.com/iam).
     * @param \Google\Cloud\ServiceControl\V1\CheckRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Check(\Google\Cloud\ServiceControl\V1\CheckRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/google.api.servicecontrol.v1.ServiceController/Check',
        $argument,
        ['\Google\Cloud\ServiceControl\V1\CheckResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Reports operation results to Google Service Control, such as logs and
     * metrics. It should be called after an operation is completed.
     *
     * If feasible, the client should aggregate reporting data for up to 5
     * seconds to reduce API traffic. Limiting aggregation to 5 seconds is to
     * reduce data loss during client crashes. Clients should carefully choose
     * the aggregation time window to avoid data loss risk more than 0.01%
     * for business and compliance reasons.
     *
     * NOTE: the [ReportRequest][google.api.servicecontrol.v1.ReportRequest] has
     * the size limit (wire-format byte size) of 1MB.
     *
     * This method requires the `servicemanagement.services.report` permission
     * on the specified service. For more information, see
     * [Google Cloud IAM](https://cloud.google.com/iam).
     * @param \Google\Cloud\ServiceControl\V1\ReportRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Report(\Google\Cloud\ServiceControl\V1\ReportRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/google.api.servicecontrol.v1.ServiceController/Report',
        $argument,
        ['\Google\Cloud\ServiceControl\V1\ReportResponse', 'decode'],
        $metadata, $options);
    }

}
