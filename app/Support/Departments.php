<?php

namespace App\Support;

/**
 * The academic departments, and every spelling each one answers to.
 *
 * One department genuinely has three names. The timesheet `department` column
 * is an enum of ('BSIT','BSBA','BSHM','EDUCATION') — there is no BSED member —
 * while the departments table was renamed EDUCATION to BSED and gained BEED in
 * the 2025_09_16 migration, and the fulltime/parttime edit forms offer both of
 * those. So a single department is stored as EDUCATION on timesheets and
 * referred to as BSED almost everywhere else.
 *
 * That split silently broke the Education attendance checker: seeded with
 * course 'bsed', they signed in fine, passed the authorisation check, and then
 * queried timesheets for department = 'BSED', which matches nothing. Their
 * register was empty forever while every other department worked.
 *
 * This is the one place the aliases live. Anything matching a department
 * against stored data should ask here rather than comparing strings itself.
 */
final class Departments
{
    /**
     * code => every value that can represent it in a `department` column.
     *
     * The first entry of each list is the canonical form.
     */
    public const ALIASES = [
        'BSIT'      => ['BSIT'],
        'BSBA'      => ['BSBA'],
        'BSHM'      => ['BSHM'],
        'EDUCATION' => ['EDUCATION', 'BSED', 'BEED'],
    ];

    /** Display names, for headings and pickers. */
    public const NAMES = [
        'BSIT'      => 'BSIT',
        'BSBA'      => 'BSBA',
        'BSHM'      => 'BSHM',
        'EDUCATION' => 'Education',
    ];

    /**
     * The canonical code for whatever spelling was supplied, or null.
     *
     * Use this to compare two department references — 'bsed' and 'EDUCATION'
     * both canonicalise to 'EDUCATION', so a strict === finally means what it
     * looks like it means.
     */
    public static function canonical(?string $course): ?string
    {
        $needle = strtoupper(trim((string) $course));

        if ($needle === '') {
            return null;
        }

        foreach (self::ALIASES as $code => $aliases) {
            if (in_array($needle, $aliases, true)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Every stored value that means this department.
     *
     * An unrecognised course returns itself rather than nothing, so a
     * department added to the database but not yet listed here still filters to
     * its own rows instead of silently matching everything.
     *
     * @return list<string>
     */
    public static function codesFor(?string $course): array
    {
        $canonical = self::canonical($course);

        if ($canonical !== null) {
            return self::ALIASES[$canonical];
        }

        $needle = strtoupper(trim((string) $course));

        return $needle === '' ? [] : [$needle];
    }

    /** True when two references point at the same department. */
    public static function same(?string $a, ?string $b): bool
    {
        $left = self::canonical($a);

        return $left !== null && $left === self::canonical($b);
    }
}
