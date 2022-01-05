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

        $uid = (int)($parameters['record']['uid'] ?? 0);

        if ($parameters['record']['sys_language_uid'] > 0) {
            $uid = (int)$parameters['record']['l10n_parent'];
        }

        if ($uid <= 0) {
            return $parameters['slug'];
        }

        $fieldSeparator = $parameters['configuration']['generatorOptions']['fieldSeparator'] ?? '/';
        $slugParts = explode(
            $fieldSeparator,
            ltrim($parameters['slug'], $fieldSeparator)
        );

        if ($this->isUidPrepended($uid, $slugParts)) {
            return $parameters['slug'];
        }

        array_unshift(
            $slugParts,
            '', // Ensure leading slash
            $uid
        );
        $slug = implode($fieldSeparator, $slugParts);

        return $slug;
    }

    private function isUidPrepended(int $uid, array $slugParts): bool
    {
        return (int)$slugParts[0] === $uid;
    }
}
