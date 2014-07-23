<?php

namespace n3b\Bundle\Kladr\Command;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\PeterStreet;


class UpdatePeterStreetCommand extends DoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('kladr:update:peter_street')
            ->addOption('batch', null, InputOption::VALUE_OPTIONAL, 'The batch size to insert. This requires dbase.so extension.', 2000)
            ->setDescription('Imports KLADR data into mysql')
            ->setHelp(<<<EOT
nothing here
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Started at ' . date('H:i:s') . '</info>');

        $em = $this->em = $this->getEntityManager('default');

        $street_repository = $this->getContainer()->get('doctrine')
          ->getRepository('n3bKladrBundle:PeterStreet');
        $peter_streets = $street_repository->findAll();

        $YMaps= $this->getContainer()->get('ymaps_service');

        $record_numbers = count($peter_streets);

        $record_numbers = count($peter_streets);
        $record_numbers = count($peter_streets);

        $batchSize = 3000;$input->getOption('batch');
        for ($i = 1; $i < $record_numbers; $i++) {
            $peter_street = $peter_streets[$i];
            if(!$peter_street->getLng())
            {
                $points = $YMaps->getCoordsFromAddress('г Санкт-Петербугр, ' .
                    $peter_street->getSocr() . ' ' . $peter_street->getTitle(),
                    array('street'));

                $peter_street->setLng($points['lng']);
                $peter_street->setLat($points['lat']);

                $em->persist($peter_street);

                if (($i % $batchSize) == 0) {
                    $em->flush();
                    $em->clear();
                    $output->writeln('<info>Inserted '. $i. ' records</info>');exit;
                }
            }
        }
        $em->flush();

        $output->writeln('<info>Inserted '. $i. ' records</info>');
        $output->writeln('<info>Success</info>');
    }
}
