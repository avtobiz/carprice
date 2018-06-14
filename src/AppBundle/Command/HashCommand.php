<?php

namespace AppBundle\Command;

use AppBundle\Repository\CarRepository;
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


class HashCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hash')
            ->setDescription('set hash');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carRepo = $this->getContainer()->get(CarRepository::class);
        $mongoClient = $this->getContainer()->get('mongodb');

        $jobId = new ObjectID('5b16e0f3fbd1b2002006ae72');
        $cars = $carRepo->findAllByJobId($jobId);

        $progress = new ProgressBar($output, count($cars));

        foreach ($cars as $car) {
            $output->write($car['_id'], true);

            $params = [];
            $params[] = $car['autoData']['autoId'];
            $params[] = $car['USD'];
            //$params[] = $car['updateDate'];

            $hash = md5(implode($params));
            $carRepo->setHash($car['_id'], $hash);
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('End...');
    }
}