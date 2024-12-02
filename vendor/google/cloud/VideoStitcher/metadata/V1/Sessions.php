<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/video/stitcher/v1/sessions.proto

namespace GPBMetadata\Google\Cloud\Video\Stitcher\V1;

class Sessions
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\FieldBehavior::initOnce();
        \GPBMetadata\Google\Api\Resource::initOnce();
        \GPBMetadata\Google\Cloud\Video\Stitcher\V1\Companions::initOnce();
        \GPBMetadata\Google\Cloud\Video\Stitcher\V1\Events::initOnce();
        \GPBMetadata\Google\Protobuf\Duration::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
-google/cloud/video/stitcher/v1/sessions.protogoogle.cloud.video.stitcher.v1google/api/resource.proto/google/cloud/video/stitcher/v1/companions.proto+google/cloud/video/stitcher/v1/events.protogoogle/protobuf/duration.proto"�

VodSession
name (	B�AI
interstitials (2-.google.cloud.video.stitcher.v1.InterstitialsB�A
play_uri (	B�A

source_uri (	B�A

ad_tag_uri (	B�AW
ad_tag_macro_map (2=.google.cloud.video.stitcher.v1.VodSession.AdTagMacroMapEntry
client_ad_tracking (I
manifest_options	 (2/.google.cloud.video.stitcher.v1.ManifestOptions4
AdTagMacroMapEntry
key (	
value (	:8:o�Al
\'videostitcher.googleapis.com/VodSessionAprojects/{project}/locations/{location}/vodSessions/{vod_session}"�
InterstitialsD
	ad_breaks (21.google.cloud.video.stitcher.v1.VodSessionAdBreakJ
session_content (21.google.cloud.video.stitcher.v1.VodSessionContent"�
VodSessionAd+
duration (2.google.protobuf.DurationC
companion_ads (2,.google.cloud.video.stitcher.v1.CompanionAds>
activity_events (2%.google.cloud.video.stitcher.v1.Event"@
VodSessionContent+
duration (2.google.protobuf.Duration"�
VodSessionAdBreakF
progress_events (2-.google.cloud.video.stitcher.v1.ProgressEvent9
ads (2,.google.cloud.video.stitcher.v1.VodSessionAd2
end_time_offset (2.google.protobuf.Duration4
start_time_offset (2.google.protobuf.Duration"�
LiveSession
name (	B�A
play_uri (	B�A

source_uri (	
default_ad_tag_id (	M

ad_tag_map (29.google.cloud.video.stitcher.v1.LiveSession.AdTagMapEntryS
ad_tag_macros (2<.google.cloud.video.stitcher.v1.LiveSession.AdTagMacrosEntry
client_ad_tracking (
default_slate_id (	U
stitching_policy	 (2;.google.cloud.video.stitcher.v1.LiveSession.StitchingPolicyI
manifest_options
 (2/.google.cloud.video.stitcher.v1.ManifestOptionsV
AdTagMapEntry
key (	4
value (2%.google.cloud.video.stitcher.v1.AdTag:82
AdTagMacrosEntry
key (	
value (	:8"g
StitchingPolicy 
STITCHING_POLICY_UNSPECIFIED 
COMPLETE_AD
COMPLETE_POD
CUT_CURRENT:r�Ao
(videostitcher.googleapis.com/LiveSessionCprojects/{project}/locations/{location}/liveSessions/{live_session}"
AdTag
uri (	"�
ManifestOptionsK
include_renditions (2/.google.cloud.video.stitcher.v1.RenditionFilterR
bitrate_order (2;.google.cloud.video.stitcher.v1.ManifestOptions.OrderPolicy"J
OrderPolicy
ORDER_POLICY_UNSPECIFIED 
	ASCENDING

DESCENDING"6
RenditionFilter
bitrate_bps (
codecs (	B}
"com.google.cloud.video.stitcher.v1BSessionsProtoPZFgoogle.golang.org/genproto/googleapis/cloud/video/stitcher/v1;stitcherbproto3'
        , true);

        static::$is_initialized = true;
    }
}

