<?php

if (!function_exists('formatKorsoItemName')) {
    function formatKorsoItemName($value)
    {
        if (!is_string($value)) return $value;

        // Make replacements
        $value = str_replace([
            'DE_EN', 'DE_UA', 'DE_AR', 'DE_ES',
            '_', '-', 'K_ECom', 'K_ECOM', 'AfA'
        ], [
            'DE/EN', 'DE/UA', 'DE/AR', 'DE/ES',
            ' ', ' ', 'K E-Com', 'K E-Com', 'AfA' // optional manual tweaks
        ], $value);

        return ucwords(trim($value));
    }
}