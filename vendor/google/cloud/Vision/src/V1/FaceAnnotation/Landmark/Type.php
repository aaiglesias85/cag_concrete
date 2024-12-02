<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/vision/v1/image_annotator.proto

namespace Google\Cloud\Vision\V1\FaceAnnotation\Landmark;

use UnexpectedValueException;

/**
 * Face landmark (feature) type.
 * Left and right are defined from the vantage of the viewer of the image
 * without considering mirror projections typical of photos. So, `LEFT_EYE`,
 * typically, is the person's right eye.
 *
 * Protobuf type <code>google.cloud.vision.v1.FaceAnnotation.Landmark.Type</code>
 */
class Type
{
    /**
     * Unknown face landmark detected. Should not be filled.
     *
     * Generated from protobuf enum <code>UNKNOWN_LANDMARK = 0;</code>
     */
    const UNKNOWN_LANDMARK = 0;
    /**
     * Left eye.
     *
     * Generated from protobuf enum <code>LEFT_EYE = 1;</code>
     */
    const LEFT_EYE = 1;
    /**
     * Right eye.
     *
     * Generated from protobuf enum <code>RIGHT_EYE = 2;</code>
     */
    const RIGHT_EYE = 2;
    /**
     * Left of left eyebrow.
     *
     * Generated from protobuf enum <code>LEFT_OF_LEFT_EYEBROW = 3;</code>
     */
    const LEFT_OF_LEFT_EYEBROW = 3;
    /**
     * Right of left eyebrow.
     *
     * Generated from protobuf enum <code>RIGHT_OF_LEFT_EYEBROW = 4;</code>
     */
    const RIGHT_OF_LEFT_EYEBROW = 4;
    /**
     * Left of right eyebrow.
     *
     * Generated from protobuf enum <code>LEFT_OF_RIGHT_EYEBROW = 5;</code>
     */
    const LEFT_OF_RIGHT_EYEBROW = 5;
    /**
     * Right of right eyebrow.
     *
     * Generated from protobuf enum <code>RIGHT_OF_RIGHT_EYEBROW = 6;</code>
     */
    const RIGHT_OF_RIGHT_EYEBROW = 6;
    /**
     * Midpoint between eyes.
     *
     * Generated from protobuf enum <code>MIDPOINT_BETWEEN_EYES = 7;</code>
     */
    const MIDPOINT_BETWEEN_EYES = 7;
    /**
     * Nose tip.
     *
     * Generated from protobuf enum <code>NOSE_TIP = 8;</code>
     */
    const NOSE_TIP = 8;
    /**
     * Upper lip.
     *
     * Generated from protobuf enum <code>UPPER_LIP = 9;</code>
     */
    const UPPER_LIP = 9;
    /**
     * Lower lip.
     *
     * Generated from protobuf enum <code>LOWER_LIP = 10;</code>
     */
    const LOWER_LIP = 10;
    /**
     * Mouth left.
     *
     * Generated from protobuf enum <code>MOUTH_LEFT = 11;</code>
     */
    const MOUTH_LEFT = 11;
    /**
     * Mouth right.
     *
     * Generated from protobuf enum <code>MOUTH_RIGHT = 12;</code>
     */
    const MOUTH_RIGHT = 12;
    /**
     * Mouth center.
     *
     * Generated from protobuf enum <code>MOUTH_CENTER = 13;</code>
     */
    const MOUTH_CENTER = 13;
    /**
     * Nose, bottom right.
     *
     * Generated from protobuf enum <code>NOSE_BOTTOM_RIGHT = 14;</code>
     */
    const NOSE_BOTTOM_RIGHT = 14;
    /**
     * Nose, bottom left.
     *
     * Generated from protobuf enum <code>NOSE_BOTTOM_LEFT = 15;</code>
     */
    const NOSE_BOTTOM_LEFT = 15;
    /**
     * Nose, bottom center.
     *
     * Generated from protobuf enum <code>NOSE_BOTTOM_CENTER = 16;</code>
     */
    const NOSE_BOTTOM_CENTER = 16;
    /**
     * Left eye, top boundary.
     *
     * Generated from protobuf enum <code>LEFT_EYE_TOP_BOUNDARY = 17;</code>
     */
    const LEFT_EYE_TOP_BOUNDARY = 17;
    /**
     * Left eye, right corner.
     *
     * Generated from protobuf enum <code>LEFT_EYE_RIGHT_CORNER = 18;</code>
     */
    const LEFT_EYE_RIGHT_CORNER = 18;
    /**
     * Left eye, bottom boundary.
     *
     * Generated from protobuf enum <code>LEFT_EYE_BOTTOM_BOUNDARY = 19;</code>
     */
    const LEFT_EYE_BOTTOM_BOUNDARY = 19;
    /**
     * Left eye, left corner.
     *
     * Generated from protobuf enum <code>LEFT_EYE_LEFT_CORNER = 20;</code>
     */
    const LEFT_EYE_LEFT_CORNER = 20;
    /**
     * Right eye, top boundary.
     *
     * Generated from protobuf enum <code>RIGHT_EYE_TOP_BOUNDARY = 21;</code>
     */
    const RIGHT_EYE_TOP_BOUNDARY = 21;
    /**
     * Right eye, right corner.
     *
     * Generated from protobuf enum <code>RIGHT_EYE_RIGHT_CORNER = 22;</code>
     */
    const RIGHT_EYE_RIGHT_CORNER = 22;
    /**
     * Right eye, bottom boundary.
     *
     * Generated from protobuf enum <code>RIGHT_EYE_BOTTOM_BOUNDARY = 23;</code>
     */
    const RIGHT_EYE_BOTTOM_BOUNDARY = 23;
    /**
     * Right eye, left corner.
     *
     * Generated from protobuf enum <code>RIGHT_EYE_LEFT_CORNER = 24;</code>
     */
    const RIGHT_EYE_LEFT_CORNER = 24;
    /**
     * Left eyebrow, upper midpoint.
     *
     * Generated from protobuf enum <code>LEFT_EYEBROW_UPPER_MIDPOINT = 25;</code>
     */
    const LEFT_EYEBROW_UPPER_MIDPOINT = 25;
    /**
     * Right eyebrow, upper midpoint.
     *
     * Generated from protobuf enum <code>RIGHT_EYEBROW_UPPER_MIDPOINT = 26;</code>
     */
    const RIGHT_EYEBROW_UPPER_MIDPOINT = 26;
    /**
     * Left ear tragion.
     *
     * Generated from protobuf enum <code>LEFT_EAR_TRAGION = 27;</code>
     */
    const LEFT_EAR_TRAGION = 27;
    /**
     * Right ear tragion.
     *
     * Generated from protobuf enum <code>RIGHT_EAR_TRAGION = 28;</code>
     */
    const RIGHT_EAR_TRAGION = 28;
    /**
     * Left eye pupil.
     *
     * Generated from protobuf enum <code>LEFT_EYE_PUPIL = 29;</code>
     */
    const LEFT_EYE_PUPIL = 29;
    /**
     * Right eye pupil.
     *
     * Generated from protobuf enum <code>RIGHT_EYE_PUPIL = 30;</code>
     */
    const RIGHT_EYE_PUPIL = 30;
    /**
     * Forehead glabella.
     *
     * Generated from protobuf enum <code>FOREHEAD_GLABELLA = 31;</code>
     */
    const FOREHEAD_GLABELLA = 31;
    /**
     * Chin gnathion.
     *
     * Generated from protobuf enum <code>CHIN_GNATHION = 32;</code>
     */
    const CHIN_GNATHION = 32;
    /**
     * Chin left gonion.
     *
     * Generated from protobuf enum <code>CHIN_LEFT_GONION = 33;</code>
     */
    const CHIN_LEFT_GONION = 33;
    /**
     * Chin right gonion.
     *
     * Generated from protobuf enum <code>CHIN_RIGHT_GONION = 34;</code>
     */
    const CHIN_RIGHT_GONION = 34;
    /**
     * Left cheek center.
     *
     * Generated from protobuf enum <code>LEFT_CHEEK_CENTER = 35;</code>
     */
    const LEFT_CHEEK_CENTER = 35;
    /**
     * Right cheek center.
     *
     * Generated from protobuf enum <code>RIGHT_CHEEK_CENTER = 36;</code>
     */
    const RIGHT_CHEEK_CENTER = 36;

    private static $valueToName = [
        self::UNKNOWN_LANDMARK => 'UNKNOWN_LANDMARK',
        self::LEFT_EYE => 'LEFT_EYE',
        self::RIGHT_EYE => 'RIGHT_EYE',
        self::LEFT_OF_LEFT_EYEBROW => 'LEFT_OF_LEFT_EYEBROW',
        self::RIGHT_OF_LEFT_EYEBROW => 'RIGHT_OF_LEFT_EYEBROW',
        self::LEFT_OF_RIGHT_EYEBROW => 'LEFT_OF_RIGHT_EYEBROW',
        self::RIGHT_OF_RIGHT_EYEBROW => 'RIGHT_OF_RIGHT_EYEBROW',
        self::MIDPOINT_BETWEEN_EYES => 'MIDPOINT_BETWEEN_EYES',
        self::NOSE_TIP => 'NOSE_TIP',
        self::UPPER_LIP => 'UPPER_LIP',
        self::LOWER_LIP => 'LOWER_LIP',
        self::MOUTH_LEFT => 'MOUTH_LEFT',
        self::MOUTH_RIGHT => 'MOUTH_RIGHT',
        self::MOUTH_CENTER => 'MOUTH_CENTER',
        self::NOSE_BOTTOM_RIGHT => 'NOSE_BOTTOM_RIGHT',
        self::NOSE_BOTTOM_LEFT => 'NOSE_BOTTOM_LEFT',
        self::NOSE_BOTTOM_CENTER => 'NOSE_BOTTOM_CENTER',
        self::LEFT_EYE_TOP_BOUNDARY => 'LEFT_EYE_TOP_BOUNDARY',
        self::LEFT_EYE_RIGHT_CORNER => 'LEFT_EYE_RIGHT_CORNER',
        self::LEFT_EYE_BOTTOM_BOUNDARY => 'LEFT_EYE_BOTTOM_BOUNDARY',
        self::LEFT_EYE_LEFT_CORNER => 'LEFT_EYE_LEFT_CORNER',
        self::RIGHT_EYE_TOP_BOUNDARY => 'RIGHT_EYE_TOP_BOUNDARY',
        self::RIGHT_EYE_RIGHT_CORNER => 'RIGHT_EYE_RIGHT_CORNER',
        self::RIGHT_EYE_BOTTOM_BOUNDARY => 'RIGHT_EYE_BOTTOM_BOUNDARY',
        self::RIGHT_EYE_LEFT_CORNER => 'RIGHT_EYE_LEFT_CORNER',
        self::LEFT_EYEBROW_UPPER_MIDPOINT => 'LEFT_EYEBROW_UPPER_MIDPOINT',
        self::RIGHT_EYEBROW_UPPER_MIDPOINT => 'RIGHT_EYEBROW_UPPER_MIDPOINT',
        self::LEFT_EAR_TRAGION => 'LEFT_EAR_TRAGION',
        self::RIGHT_EAR_TRAGION => 'RIGHT_EAR_TRAGION',
        self::LEFT_EYE_PUPIL => 'LEFT_EYE_PUPIL',
        self::RIGHT_EYE_PUPIL => 'RIGHT_EYE_PUPIL',
        self::FOREHEAD_GLABELLA => 'FOREHEAD_GLABELLA',
        self::CHIN_GNATHION => 'CHIN_GNATHION',
        self::CHIN_LEFT_GONION => 'CHIN_LEFT_GONION',
        self::CHIN_RIGHT_GONION => 'CHIN_RIGHT_GONION',
        self::LEFT_CHEEK_CENTER => 'LEFT_CHEEK_CENTER',
        self::RIGHT_CHEEK_CENTER => 'RIGHT_CHEEK_CENTER',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Type::class, \Google\Cloud\Vision\V1\FaceAnnotation_Landmark_Type::class);

