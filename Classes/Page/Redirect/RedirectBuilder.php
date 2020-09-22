<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Pagemachine\FlatUrls\Page\Page;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class RedirectBuilder
{
    /**
     * @var Redirect
     */
    private $redirect;

    public function build(Page $page): Redirect
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $pageData = $queryBuilder
            ->select('sys_language_uid', 'slug')
            ->from('pages')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($page->getUid())
            ))
            ->execute()
            ->fetch();

        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($page->getUid());
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
