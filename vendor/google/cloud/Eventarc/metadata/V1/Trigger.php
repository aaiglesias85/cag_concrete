<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/eventarc/v1/trigger.proto

namespace GPBMetadata\Google\Cloud\Eventarc\V1;

class Trigger
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\FieldBehavior::initOnce();
        \GPBMetadata\Google\Api\Resource::initOnce();
        \GPBMetadata\Google\Protobuf\Timestamp::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
&google/cloud/eventarc/v1/trigger.protogoogle.cloud.eventarc.v1google/api/resource.protogoogle/protobuf/timestamp.proto"�
Trigger
name (	B�A
uid (	B�A4
create_time (2.google.protobuf.TimestampB�A4
update_time (2.google.protobuf.TimestampB�AD
event_filters (2%.google.cloud.eventarc.v1.EventFilterB�A�AB
service_account	 (	B)�A�A#
!iam.googleapis.com/ServiceAccount?
destination
 (2%.google.cloud.eventarc.v1.DestinationB�A;
	transport (2#.google.cloud.eventarc.v1.TransportB�AB
labels (2-.google.cloud.eventarc.v1.Trigger.LabelsEntryB�A
channel (	B�A
etagc (	B�A-
LabelsEntry
key (	
value (	:8:s�Ap
eventarc.googleapis.com/Trigger:projects/{project}/locations/{location}/triggers/{trigger}*triggers2trigger"P
EventFilter
	attribute (	B�A
value (	B�A
operator (	B�A"�
Destination7
	cloud_run (2".google.cloud.eventarc.v1.CloudRunH J
cloud_function (	B0�A-
+cloudfunctions.googleapis.com/CloudFunctionH ,
gke (2.google.cloud.eventarc.v1.GKEH B

descriptor"O
	Transport2
pubsub (2 .google.cloud.eventarc.v1.PubsubH B
intermediary"g
CloudRun3
service (	B"�A�A
run.googleapis.com/Service
path (	B�A
region (	B�A"s
GKE
cluster (	B�A
location (	B�A
	namespace (	B�A
service (	B�A
path (	B�A"7
Pubsub
topic (	B�A
subscription (	B�AB�
com.google.cloud.eventarc.v1BTriggerProtoPZ@google.golang.org/genproto/googleapis/cloud/eventarc/v1;eventarc�Ak
+cloudfunctions.googleapis.com/CloudFunction<projects/{project}/locations/{location}/functions/{function}�AY
!iam.googleapis.com/ServiceAccount4projects/{project}/serviceAccounts/{service_account}�A
run.googleapis.com/Service*bproto3'
        , true);

        static::$is_initialized = true;
    }
}

