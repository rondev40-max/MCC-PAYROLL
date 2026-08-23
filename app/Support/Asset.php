<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Cache-busted URLs for the files served straight out of public/.
 *
 * This project ships its CSS and JS as plain files rather than through Vite,
 * and every view linked them with a bare asset() call — no version, no hash.
 * Browsers cache those aggressively, and this host has LiteSpeed in front of
 * it too, so an edited stylesheet could keep serving its old copy long after
 * the fix shipped. That is exactly how the attendance register kept showing
 * "Loading attendance register", "The register could not be loaded" and "No
 * personnel found" stacked over a table that had loaded perfectly: the CSS
 * that made [hidden] work was on disk, but nobody's browser was asking for it.
 *
 * Appending the file's modification time gives each revision its own URL, so a
 * change reaches everyone on their next page load and unchanged files stay
 * cached.
 */
final class Asset
{
    /** Cache of resolved mtimes, so one request stats each file at most once. */
    private static array $versions = [];

    /**
     * A public asset URL carrying the file's revision.
     *
     * Falls back to the plain URL when the file cannot be read — a missing
     * stylesheet should look like a missing stylesheet, not a 500.
     */
    public static function versioned(string $path): string
    {
        $path = ltrim($path, '/');
        $url  = asset($path);

        if (!array_key_exists($path, self::$versions)) {
            self::$versions[$path] = self::modifiedTime($path);
        }

        $version = self::$versions[$path];

        if ($version === null) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }

    private static function modifiedTime(string $path): ?int
    {
        try {
            $full = public_path($path);

            if (!is_file($full)) {
                Log::warning('Versioned asset is missing from public/.', ['path' => $path]);

                return null;
            }

            return filemtime($full) ?: null;
        } catch (\Throwable $e) {
            Log::warning('Could not read asset modification time.', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
