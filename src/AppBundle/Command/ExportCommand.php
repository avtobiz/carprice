<?php

namespace AppBundle\Command;

use AppBundle\Repository\JobRepository;
use AppBundle\Service\API\RiaClient;
use Interop\Queue\Exception;
use MongoDB\BSON\ObjectID;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Settings;
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


class ExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('export')
            ->setDescription('Get information about auto');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->getParameter('kernel.root_dir') . '/../public/data/';

        //5b16e0f3fbd1b2002006ae72
        //5b17856afbd1b200254c23d2
        $mongoClient = $this->getContainer()->get('mongodb');
        $filter = [];
        $filter = ['job' => new ObjectID('5b199306fbd1b2000f3951e2')];
        $options = [];

        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize ' => '256MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);


        $query = new \MongoDB\Driver\Query($filter, $options);
        $items   = $mongoClient->getManager()->executeQuery('ria_auto.cars', $query);

        $workbook = new Workbook();
        $sheet = new Sheet($workbook);
        $table = new Table();

        $table->setRow([
            'ID',
            'Title',
            'Add Date',
            'Expire Date',
            'Phone',
            'Phone ID',
            'User ID',
            'USD',
            'Mark name',
            'Model name',
            'Link',
            'Update Date',
            'City',
            'Year car',
            'Sold'
        ]);

        $progress = new ProgressBar($output, count($items));

        foreach ($items as $document) {
            $progress->advance();
            $data = [
                $document->autoData->autoId,
                $document->title,
                $document->addDate,
                $document->expireDate,
                $document->userPhoneData->phone,
                $document->userPhoneData->phoneId,
                $document->userId,
                $document->USD,
                $document->markName,
                $document->modelName,
                'https://auto.ria.com'.$document->linkToView
            ];
            if (isset($document->updateDate)) {
                $data[] = $document->updateDate;
            } else {
                $data[] = 'NULL';
            }
            $data[] = $document->locationCityName;
            $data[] = $document->autoData->year;
            $data[] = ($document->autoData->isSold)?'YES':'NO';


            $table->setRow($data);
        }

        $sheet->addTable($table, new Coordinate(1, 1));
        $workbook->addSheet($sheet);


        $fileName = 'Export-'.date('Y-m-d').'.xls';
        $writer = (new WriterFactory())->createWriter(new Excel5($path.$fileName));
        $phpExcel = $writer->convert($workbook);
        $writer->write($phpExcel);
        $progress->finish();


        $output->writeln('End...', true);
    }
}
