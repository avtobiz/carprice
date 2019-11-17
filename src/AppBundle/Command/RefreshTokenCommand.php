<?php

namespace AppBundle\Command;

use AppBundle\Repository\TokenKeeperRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class RefreshTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('refresh-token')
            ->setDescription('Refresh token');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokenKeeper = $this->getContainer()->get(TokenKeeperRepository::class);
        $tokenKeeper->refreshTokens();
    }
}
