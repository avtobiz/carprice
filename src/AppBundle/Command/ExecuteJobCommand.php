<?php

namespace AppBundle\Command;

use AppBundle\Repository\CarRepository;
use AppBundle\Repository\JobRepository;
use AppBundle\Repository\TokenKeeperRepository;
use AppBundle\Service\API\RiaClient;
use Interop\Queue\Exception;
use MongoDB\BSON\ObjectID;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ExcelAnt\Adapter\PhpExcel\Workbook\Workbook,
    ExcelAnt\Adapter\PhpExcel\Sheet\Sheet,
    ExcelAnt\Adapter\PhpExcel\Writer\Writer,
    ExcelAnt\Table\Table,
    ExcelAnt\Coordinate\Coordinate;

use ExcelAnt\Adapter\PhpExcel\Writer\WriterFactory,
    ExcelAnt\Adapter\PhpExcel\Writer\PhpExcelWriter\Excel5;


class ExecuteJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('execute-job')
            ->setDescription('Execute job by ID');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('logger');
        $jobRepo = $this->getContainer()->get(JobRepository::class);
        $tokenKeeper = $this->getContainer()->get(TokenKeeperRepository::class);
        $mongoClient = $this->getContainer()->get('mongodb');
        $client = new RiaClient($tokenKeeper);

        $job = $jobRepo->getJobForExecute();

        if (is_null($job)) {
            $logger->addError(sprintf('Not found job'));

            return false;
        }

        $logger->info(sprintf('Execute job ID:"%s"', $job['_id']));
        $iteration = 0;
        $batchSize = 50;
        $completedTasks = [];
        $isOverLimit = false;

        $bulk = new \MongoDB\Driver\BulkWrite;
        $progress = new ProgressBar($output, count($job->tasks));

        foreach ($job->tasks as $id) {
            $iteration++;
            $progress->advance();

            sleep(0.01);

            $res = $client->infoAutoById($id);

            if (in_array($res->getStatusCode(), [200])) {
                $data = json_decode($res->getBody()->getContents(), true);

                if (is_null($data['autoData'])){
                    echo $id;
                    continue;
                }

                $data['createdAt'] = (new \MongoDB\BSON\UTCDateTime(time() * 1000));
                $data['job'] = $job['_id'];

                $bulk->insert($data);
                $completedTasks[] = $data['autoData']['autoId'];

                if (($iteration % $batchSize) === 0) {
                    $mongoClient->getManager()->executeBulkWrite('ria_auto_1.cars', $bulk);
                    unset($bulk);
                    $bulk = new \MongoDB\Driver\BulkWrite;
                    $jobRepo->setCompletedTasksForJob($job['_id'], $completedTasks);
                    $completedTasks = [];
                }
            } else {
                $logger->addError(sprintf('Bad response status code: "%s"', $res->getStatusCode()));

                if (in_array($res->getStatusCode(), [502])) {
                    continue;
                }

                if (in_array($res->getStatusCode(), [429])) {
                    $isOverLimit = true;
                    continue;
                }
            }
        }

        $mongoClient->getManager()->executeBulkWrite('ria_auto_1.cars', $bulk);
        unset($bulk);
        $progress->finish();
        $jobRepo->updateStatus($job['_id'], 'completed');
        $jobRepo->setCompletedTasksForJob($job['_id'], $completedTasks);
        unset($completedTasks);

        if ($isOverLimit) {
            $logger->addError(sprintf('Exit over limit'));

            return false;
        }
    }
}
