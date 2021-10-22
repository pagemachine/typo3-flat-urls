<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect\Conflict;

use Pagemachine\FlatUrls\Page\Page;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class RedirectConflictDetector
{
    /**
     * @return \Generator|ConflictRedirect[]
     */
    public function detect(Page $page): \Generator
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            $site = $siteFinder->getSiteByPageId($page->getUid());
        } catch (SiteNotFoundException $e) {
            return;
        }

        $pageUri = $this->buildPageUri($page, $site);
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');

        // Redirects with a source path exactly like the target page path
        $redirects = $connection->select(
            ['uid'],
            'sys_redirect',
            [
                'source_path' => rtrim($pageUri->getPath(), '/'),
            ]
        );

        foreach ($redirects as $redirect) {
            yield new ConflictRedirect($redirect['uid']);
        }

        // Redirects with just the UID as source path: conflict with custom redirect handling
        $redirects = $connection->select(
            ['uid'],
            'sys_redirect',
            [
                'source_path' => sprintf('/%d', $page->getUid()),
            ]
        );

        foreach ($redirects as $redirect) {
            yield new ConflictRedirect($redirect['uid']);
        }
    }

    private function buildPageUri(Page $page, Site $site): UriInterface
    {
        $pageLanguage = $this->getLanguageOfPage($page);
        $pageUri = $site->getRouter()->generateUri(
            (string)$page->getUid(),
            ['_language' => $pageLanguage]
        );

        return $pageUri;
    }

    private function getLanguageOfPage(Page $page): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $pageLanguage = $queryBuilder
            ->select('sys_language_uid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($page->getUid())
            ))
            ->execute()
            ->fetchColumn();

        return $pageLanguage;
    }
}
