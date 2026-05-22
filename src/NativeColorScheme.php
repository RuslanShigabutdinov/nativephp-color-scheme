<?php

namespace RuslanShigabutdinov\NativeColorScheme;

final class NativeColorScheme
{
    public const METHOD_GET = 'NativeColorScheme.Get';

    public const METHOD_SET_PREFERENCE = 'NativeColorScheme.SetPreference';

    public const PREFERENCE_SYSTEM = 'system';

    public const PREFERENCE_LIGHT = 'light';

    public const PREFERENCE_DARK = 'dark';

    public function get(): ?string
    {
        return $this->getColorScheme();
    }

    public function getColorScheme(): ?string
    {
        $state = $this->state();

        return $this->normalizeColorScheme($state['colorScheme'] ?? null);
    }

    public function getSystemColorScheme(): ?string
    {
        $state = $this->state();

        return $this->normalizeColorScheme($state['systemColorScheme'] ?? null);
    }

    public function getPreference(): ?string
    {
        $state = $this->state();

        return $this->normalizePreference($state['preference'] ?? null);
    }

    /**
     * @return array{
     *     preference: string,
     *     colorScheme: string,
     *     systemColorScheme: string,
     *     isSystem: bool,
     *     isDark: bool,
     *     isLight: bool
     * }|null
     */
    public function state(): ?array
    {
        $payload = $this->call(self::METHOD_GET);
        if ($payload === null) {
            return null;
        }

        $preference = $this->normalizePreference($payload['preference'] ?? null) ?? self::PREFERENCE_SYSTEM;
        $systemColorScheme = $this->normalizeColorScheme($payload['systemColorScheme'] ?? null) ?? self::PREFERENCE_LIGHT;
        $colorScheme = $this->normalizeColorScheme($payload['colorScheme'] ?? null)
            ?? ($preference === self::PREFERENCE_SYSTEM ? $systemColorScheme : $preference);

        return [
            'preference' => $preference,
            'colorScheme' => $colorScheme,
            'systemColorScheme' => $systemColorScheme,
            'isSystem' => $preference === self::PREFERENCE_SYSTEM,
            'isDark' => $colorScheme === self::PREFERENCE_DARK,
            'isLight' => $colorScheme === self::PREFERENCE_LIGHT,
        ];
    }

    public function setPreference(string $preference): bool
    {
        $preference = $this->normalizePreference($preference);
        if ($preference === null) {
            return false;
        }

        $payload = $this->call(self::METHOD_SET_PREFERENCE, [
            'preference' => $preference,
        ]);

        return $this->normalizePreference($payload['preference'] ?? null) === $preference;
    }

    public function useSystem(): bool
    {
        return $this->setPreference(self::PREFERENCE_SYSTEM);
    }

    public function useLight(): bool
    {
        return $this->setPreference(self::PREFERENCE_LIGHT);
    }

    public function useDark(): bool
    {
        return $this->setPreference(self::PREFERENCE_DARK);
    }

    public function isSystem(): bool
    {
        return $this->getPreference() === self::PREFERENCE_SYSTEM;
    }

    public function isSystemDark(): bool
    {
        return $this->getSystemColorScheme() === self::PREFERENCE_DARK;
    }

    public function isSystemLight(): bool
    {
        return $this->getSystemColorScheme() === self::PREFERENCE_LIGHT;
    }

    public function isDark(): bool
    {
        return $this->getColorScheme() === self::PREFERENCE_DARK;
    }

    public function isLight(): bool
    {
        return $this->getColorScheme() === self::PREFERENCE_LIGHT;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function call(string $method, array $parameters = []): ?array
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        if (function_exists('nativephp_can') && ! nativephp_can($method)) {
            return null;
        }

        $result = nativephp_call($method, json_encode($parameters, JSON_THROW_ON_ERROR));
        if (! is_string($result) || $result === '') {
            return null;
        }

        $payload = json_decode($result, true);
        if (! is_array($payload) || ($payload['status'] ?? null) === 'error') {
            return null;
        }

        return $payload;
    }

    private function normalizeColorScheme(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $scheme = strtolower(trim($value, " \t\n\r\0\x0B\"'"));

        return in_array($scheme, ['light', 'dark'], true) ? $scheme : null;
    }

    private function normalizePreference(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value, " \t\n\r\0\x0B\"'"))) {
            'auto', 'device', 'os', self::PREFERENCE_SYSTEM => self::PREFERENCE_SYSTEM,
            self::PREFERENCE_LIGHT => self::PREFERENCE_LIGHT,
            self::PREFERENCE_DARK => self::PREFERENCE_DARK,
            default => null,
        };
    }
}
