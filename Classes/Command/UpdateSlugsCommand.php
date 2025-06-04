<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Command;

use Pagemachine\FlatUrls\Page\PageCollection;
use Pagemachine\FlatUrls\Page\Slug\PageSlugProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;

final class UpdateSlugsCommand extends Command
{
    public function __construct(
        private PageCollection $pageCollection,
        private PageSlugProcessor $slugProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update slugs of all pages');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Bootstrap::initializeBackendAuthentication();

        $progress = new ProgressBar($output);

        foreach ($progress->iterate($this->pageCollection) as $page) {
            $this->slugProcessor->update($page);
        }

        $output->writeln('');

        return 0;
    }
}
