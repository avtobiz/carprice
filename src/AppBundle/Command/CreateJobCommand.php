<?php

namespace AppBundle\Command;

use AppBundle\Repository\JobRepository;
use AppBundle\Service\API\RiaClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CreateJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('create-job')
            ->setDescription('Generate job');
    }

    public function iteratePages($page, $jobId, RiaClient $client, $jobRepo)
    {
        sleep(3);
        $params = ['state' => [4],'city'=>[0], 'countpage' => 100, 'saledParam' => 0, 'top' => 0, 'page' => $page, 'category_id' => 1];

        //API KEY
        $params['api_key'] = 'hblEVdm9aasEsWL54Mcj5wzD1bCnPJiOKHa7h23C';

        $res = $client->searchAuto($params);

        if (in_array($res->getStatusCode(), [200])) {
            $data = json_decode($res->getBody()->getContents(), true);

            /** @var JobRepository $jobRepo */
            $jobRepo->addTasksForJob($jobId, $data['result']['search_result']['ids']);

        } else {
            echo $res->getStatusCode();
            throw new \Exception('Bad response');
        }
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new RiaClient();
        $logger = $this->getContainer()->get('logger');
        $params = ['state' => [4],'city'=>[0], 'countpage' => 100, 'saledParam' => 0, 'top' => 0, 'page' => 0, 'category_id' => 1];

        //API KEY
        $params['api_key'] = 'hblEVdm9aasEsWL54Mcj5wzD1bCnPJiOKHa7h23C';

        $res = $client->searchAuto($params);

        $jobRepo = $this->getContainer()->get(JobRepository::class);

        if (in_array($res->getStatusCode(), [200])) {
            $array = json_decode($res->getBody()->getContents(), true);
            $count = $array['result']['search_result']['count'];

            $logger->info(sprintf("Found items: %s", $count));

            $countpage = $params['countpage'];
            $pages = round($count / $countpage)+1;

            $jobId = $jobRepo->createJob($params, $count);

            for ($p = 0; $p <= $pages; $p++) {
                $this->iteratePages($p, $jobId, $client, $jobRepo);
            }

            $logger->info(sprintf("Create job: %s", $jobId));
            $output->write($jobId, true);
        } else {
            $logger->error(sprintf("Bad response, status code %s", $res->getStatusCode()));
        }
    }
}