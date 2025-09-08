<?php

namespace App\Enums;

class DeviceStatus
{
    const AVAILABLE = 'Verfügbar';
    const UNAVAILABLE = 'Nicht verfügbar';
    const ORDERED = 'Bestellt';
    const RECEIVED = 'Erhalten';
    const IN_PLACE = 'An Ort';

    /**
     * Get all the enum values.
     *
     * @return array
     */
    public static function getValues()
    {
        return [
            self::AVAILABLE,
            self::UNAVAILABLE,
            self::ORDERED,
            self::RECEIVED,
            self::IN_PLACE,
        ];
    }
}