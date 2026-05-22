<?php

namespace RuslanShigabutdinov\NativeColorScheme;

final class NativeColorScheme
{
    public const BRIDGE_METHOD = 'NativeColorScheme.Get';

    public function get(): ?string
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        if (function_exists('nativephp_can') && ! nativephp_can(self::BRIDGE_METHOD)) {
            return null;
        }

        $result = nativephp_call(self::BRIDGE_METHOD, '{}');
        if (! is_string($result) || $result === '') {
            return null;
        }

        $payload = json_decode($result, true);
        if (! is_array($payload) || ($payload['status'] ?? null) === 'error') {
            return null;
        }

        return $this->normalize(
            $payload['colorScheme']
                ?? $payload['color_scheme']
                ?? $payload['scheme']
                ?? null
        );
    }

    public function isDark(): bool
    {
        return $this->get() === 'dark';
    }

    public function isLight(): bool
    {
        return $this->get() === 'light';
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $scheme = strtolower(trim($value, " \t\n\r\0\x0B\"'"));

        return in_array($scheme, ['light', 'dark'], true) ? $scheme : null;
    }
}
