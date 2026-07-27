<?php

if (!function_exists('formatKorsoItemName')) {
    function formatKorsoItemName($value)
    {
        if (!is_string($value)) return $value;

        // Make replacements
        $value = str_replace([
            'DE_EN', 'DE_UA', 'DE_AR', 'DE_ES',
            '_', '-','AfA'
        ], [
            'DE/EN', 'DE/UA', 'DE/AR', 'DE/ES',
            ' ', ' ', 'AfA' // optional manual tweaks
        ], $value);

        return ucwords(trim($value));
    }
}