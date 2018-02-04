<?php

namespace AppBundle\Command;

use AppBundle\Repository\JobRepository;
use AppBundle\Service\API\RiaClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class AutoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api')
            ->setDescription('Get information about auto');
    }

    public function iteratePages($page, $jobId, RiaClient $client, $jobRepo)
    {
        sleep(3);
        $params = ['state' => [4],'city'=>[0], 'countpage' => 100, 'saledParam' => 0, 'top' => 0, 'page' => $page, 'category_id' => 1];

        $res = $client->searchAuto($params);

        if (in_array($res->getStatusCode(), [200])) {
            $data = json_decode($res->getBody()->getContents(), true);

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
        $params = ['state' => [4],'city'=>[0], 'countpage' => 100, 'saledParam' => 0, 'top' => 0, 'page' => 0, 'category_id' => 1];
        $res = $client->searchAuto($params);

        $jobRepo = $this->getContainer()->get(JobRepository::class);

        if (in_array($res->getStatusCode(), [200])) {
            $output->write('Success request', true);

            $array = json_decode($res->getBody()->getContents(), true);
            $count = $array['result']['search_result']['count'];
            $output->write(sprintf("Found items: %s", $count), true);


            $countpage = $params['countpage'];
            $pages = round($count / $countpage)+1;

            $output->write(sprintf("Total pages: %s", $pages), true);

            $jobId = $jobRepo->createJob($params, $count);
            $output->write(sprintf("Create job: %s", $jobId), true);

            for ($p = 0; $p <= $pages; $p++) {
                $output->write(sprintf('save page %s', $p), true);
                $this->iteratePages($p, $jobId, $client, $jobRepo);
            }

//            var_dump($array['result']['search_result']);
//            foreach ($array['result']['search_result']['ids'] as $id) {

//                $data = $s->getById($id);
//                echo $data['userId'].PHP_EOL;
//                addDate
//                updateDate
//                expireDate

//                userPhoneData['phoneId']
//                userPhoneData['phone']
//                USD
//                UAH
//                EUR
//                linkToView
//                title

//                autoId
//
//                силка ціна дата
//            }

//            database My SQL
//            Car
//            id
//            userID
//                        version
//            advertID



            //

            $output->writeln('End...');
        } else {
            $output->writeln(sprintf("Bad response, status code %s", $res->getStatusCode()), true);
        }
    }
}