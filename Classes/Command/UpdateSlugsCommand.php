<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Command;

use Pagemachine\FlatUrls\Page\PageCollection;
use Pagemachine\FlatUrls\Page\SlugProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class UpdateSlugsCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Update slugs of all pages');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Bootstrap::initializeBackendAuthentication();

        /** @var PageCollection */
        $pages = GeneralUtility::makeInstance(PageCollection::class);
        $slugProcessor = GeneralUtility::makeInstance(SlugProcessor::class);
        $progress = new ProgressBar($output);

        foreach ($progress->iterate($pages) as $page) {
            $slugProcessor->update($page);
        }

        $output->writeln('');

        return 0;
    }
}
