<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Slot;

final class ExtensionManagementUtilitySlot
{
    public function prependUidToPageSlug(array $tablesConfiguration): array
    {
        array_unshift(
            $tablesConfiguration['pages']['columns']['slug']['config']['generatorOptions']['fields'],
            [
                'l10n_parent',
                'uid',
            ]
        );

        return [
            $tablesConfiguration,
        ];
    }
}
