<?php

namespace AppBundle\Command;

use AppBundle\Repository\JobRepository;
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


class AutoInfoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api-info')
            ->setDescription('Get information about auto');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new RiaClient();
        $jobRepo = $this->getContainer()->get(JobRepository::class);
        $mongoClient = $this->getContainer()->get('mongodb');
        $job = $jobRepo->findById(new ObjectID('5a0560dfccb0c6004203b392'));

        if (is_null($job)) {
            throw new Exception('No found job by ID');
        }

        $iteration = 0;
        $batchSize = 50;
        $i = 0;
        $completedTasks = [];

        $bulk = new \MongoDB\Driver\BulkWrite;
        $progress = new ProgressBar($output, count($job->tasks));

        foreach ($job->tasks as $id) {
            $iteration++;
            $progress->advance();

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

                // Handle user
                // Handle phone

                if (($iteration % $batchSize) === 0) {
                    $mongoClient->getManager()->executeBulkWrite('ria_auto.cars', $bulk);
                    unset($bulk);
                    $bulk = new \MongoDB\Driver\BulkWrite;
                    $jobRepo->setCompletedTasksForJob($job['_id'], $completedTasks);
                    $completedTasks = [];
                }
            } else {
                $output->write('Error!!!!', true);
                $output->write('Status code: ' . $res->getStatusCode(), true);
            }
        }

        $mongoClient->getManager()->executeBulkWrite('ria_auto.cars', $bulk);
        unset($bulk);
        $progress->finish();
        $jobRepo->setCompletedTasksForJob($job['_id'], $completedTasks);
        unset($completedTasks);

        $output->writeln('End...');
    }
}