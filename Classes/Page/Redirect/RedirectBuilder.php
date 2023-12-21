<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Pagemachine\FlatUrls\Page\Page;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\SiteFinder;

final class RedirectBuilder
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private SiteFinder $siteFinder,
    ) {}

    public function build(Page $page): Redirect
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $pageData = $queryBuilder
            ->select('sys_language_uid', 'l10n_source', 'l10n_parent', 'slug')
            ->from('pages')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($page->getUid())
            ))
            ->executeQuery()
            ->fetchAssociative();

        $pageIdInSite = $page->getUid();

        if ($pageData['sys_language_uid'] > 0) {
            $pageIdInSite = $pageData['l10n_source'] ?: $pageData['l10n_parent'];
        }

        try {
            $site = $this->siteFinder->getSiteByPageId($pageIdInSite);
        } catch (SiteNotFoundException $e) {
            throw new BuildFailureException(sprintf('Missing site for redirect: %s', $e->getMessage()), 1601628182, $e);
        }

        $siteLanguage = $site->getLanguageById($pageData['sys_language_uid']);
        $sourceUri = (new Uri())
            ->withHost($site->getBase()->getHost())
            ->withPort($site->getBase()->getPort())
            ->withPath(rtrim($siteLanguage->getBase()->getPath(), '/') . $pageData['slug']);
        $targetUri = new Typo3Uri(sprintf('t3://page?uid=%d', $page->getUid()));

        $redirect = new Redirect(
            $page,
            $sourceUri,
            $targetUri,
            307
        );

        return $redirect;
    }
}
