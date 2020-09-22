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

        $fieldSeparator = $parameters['configuration']['generatorOptions']['fieldSeparator'] ?? '/';
        $slugParts = [''];

        if ($parameters['record']['sys_language_uid'] > 0) {
            $slugParts[] = $parameters['record']['l10n_parent'];
        } else {
            $slugParts[] = $parameters['record']['uid'];
        }

        $slugParts[] = ltrim($parameters['slug'], $fieldSeparator);

        $slug = implode($fieldSeparator, $slugParts);

        return $slug;
    }
}
