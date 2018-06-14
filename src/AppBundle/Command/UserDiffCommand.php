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


class UserDiffCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user-diff')
            ->setDescription('find user diff');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $carRepo = $this->getContainer()->get(CarRepository::class);
        $mongoClient = $this->getContainer()->get('mongodb');

//        $jobMain = new ObjectID('5b17856afbd1b200254c23d2');
//        $jobRelative = new ObjectID('5b16e0f3fbd1b2002006ae72');
        $jobMain = new ObjectID('5b16e0f3fbd1b2002006ae72');


        $cars = $carRepo->findAllByJobId($jobMain);

//        $progress = new ProgressBar($output, $carRepo->countByJobId($jobMain));

        foreach ($cars as $car) {
//            $progress->advance();

            $count = $carRepo->countByPhoneJob((int)$car['userId'], $jobMain);

            if ($count <= 1) {
                continue;
            }

            $output->writeln(sprintf('%s : %s', $car['userPhoneData']['phoneId'], (string)$count), true);

        }

//        $progress->finish();
        $output->writeln('End...');
    }
}