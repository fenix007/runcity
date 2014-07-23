<?php

namespace n3b\Bundle\Kladr\Command;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\PeterStreet;


class ImportPeterStreetCommand extends DoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('kladr:import:peter_street')
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

        $this->truncate();

        $region_repository = $this->getContainer()->get('doctrine')
          ->getRepository('n3bKladrBundle:KladrRegion');
        $peter = $region_repository->findOneBy(
                    array('title' => 'Санкт-Петербург', 'socr' => 'г')
        );

        $street_repository = $this->getContainer()->get('doctrine')
          ->getRepository('n3bKladrBundle:KladrStreet');
        $peter_streets = $street_repository->findBy(
                    array('parentCode' => $peter->getId())
        );

        $record_numbers = count($peter_streets);

        $batchSize = $input->getOption('batch');
        for ($i = 1; $i < $record_numbers; $i++) {
            $peter_street = $peter_streets[$i];

            $street = new peterStreet();
            $street->setSocr($peter_street->getSocr());
            $street->setTitle($peter_street->getTitle());
            $street->setZip($peter_street->getZip());
            $street->setOcatd($peter_street->getOcatd());

            $em->persist($street);

            if (($i % $batchSize) == 0) {
                $em->flush();
                $em->clear();
                $output->writeln('<info>Inserted '. $i. ' records</info>');
            }
        }
        $em->flush();

        $output->writeln('<info>Inserted '. $i. ' records</info>');
        $output->writeln('<info>Success</info>');
    }

    public function truncate()
    {
        $sql = "TRUNCATE TABLE PeterStreet";
        $stmt = $this->em->getConnection()->prepare($sql);

        return $stmt->execute();
    }
}
