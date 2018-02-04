<?php

namespace AppBundle\Command;

use AppBundle\Repository\JobRepository;
use AppBundle\Service\API\RiaClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class BookCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('book')->setDescription('Get information about auto');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->getParameter('kernel.root_dir') . '/../public/data/';
        for ($i = 152; $i <= 200; $i++) {
            $output->write(' > ' . $i, true);
            $client = new \GuzzleHttp\Client();
            $query = ['lang' => 0, 'doc_dir' => 'eng', 'doc_num' => $i];
            $res = $client->request('GET', 'http://uchisdarom.com.ua/player.php', ['query' => $query]);
            if (in_array($res->getStatusCode(), [200])) {
                $crawler = new Crawler((string)$res->getBody());
                $link = $crawler->filterXPath('descendant-or-self::p/a[@class="button"]')->attr('href');
                $title = $crawler->filterXPath('descendant-or-self::p/b')->text();
                $matches = [];
                preg_match('/\((.+)\)/', $title, $matches);
                $title = str_replace('/', '_', str_replace(' ', '_', strtolower($matches[1])));

                $filename = sprintf("%s__%s.mp3", $i, $title);
                $resource = fopen($path.$filename, 'w');
                $client->request('GET', $link, ['sink' => $resource]);

            } else {
                $output->writeln(sprintf("Bad response, status code %s", $res->getStatusCode()), true);
            }
        }
    }
}