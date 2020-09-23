<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Slug;

final class PageSlugModifier
{
    public function prependUid(array $parameters): string
    {
        if ($parameters['tableName'] !== 'pages') {
            return $parameters['slug'];
        }

        $uid = $parameters['record']['uid'] ?? 0;

        if ($parameters['record']['sys_language_uid'] > 0) {
            $uid = $parameters['record']['l10n_parent'];
        }

        if ($uid <= 0) {
            return $parameters['slug'];
        }

        $fieldSeparator = $parameters['configuration']['generatorOptions']['fieldSeparator'] ?? '/';
        $slug = implode($fieldSeparator, [
            '', // Ensure leading slash
            $uid,
            ltrim($parameters['slug'], $fieldSeparator),
        ]);

        return $slug;
    }
}
