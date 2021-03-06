<?php

namespace n3b\Bundle\Kladr\Command;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\KladrStreet;

class ImportStreetCommand extends DoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('kladr:import:street')
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

        //$this->truncate();
        $street_repository = $this->getContainer()->get('doctrine')
            ->getRepository('n3bKladrBundle:KladrStreet');
        $numInDb = $street_repository->createQueryBuilder('ks')
            ->select('COUNT(ks.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $db_path = __DIR__ . '/../Resources/KLADR/STREET.DBF';
        $db = dbase_open($db_path, 0) or die("Error! Could not open dbase database file '$db_path'.");
        $record_numbers = dbase_numrecords($db);

        $batchSize = $input->getOption('batch');
        for ($i = 1; $i <= $record_numbers; $i++) {
            $row = dbase_get_record_with_names($db, $i);

            $street = new KladrStreet();
            $street->setTitle(trim(iconv('cp866', 'utf8', $row['NAME'])));

            $code = trim($row['CODE']);
            if(substr($code, -2) != '00')
                continue;

            $code = substr($code, 0, -2);
            if ($street_repository->findOneById($code)) {
                continue;
            }
            $street->setId($code);
            $street->setParentCode(str_pad(substr($code, 0, -4), 20, '0', STR_PAD_RIGHT));

            $street->setZip(trim($row['INDEX']));
            $street->setOcatd(trim($row['OCATD']));
            $street->setSocr(trim(iconv('cp866', 'utf8', $row['SOCR'])));

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
        $output->writeln('<info>Deleting dead links</info>');
        //$this->deleteNotLinkedElements();
        $output->writeln('<info>Assigning parents</info>');
        //$this->updateParents();
        $output->writeln('<info>Success</info>');
    }

    public function truncate()
    {
        $sql = "TRUNCATE TABLE KladrStreet";
        $stmt = $this->em->getConnection()->prepare($sql);

        return $stmt->execute();
    }

    public function updateParents()
    {
        $sql = "
            UPDATE KladrStreet s
            SET s.parent_id = s.parentCode";
        $stmt = $this->em->getConnection()->prepare($sql);

        return $stmt->execute();
    }

    public function deleteNotLinkedElements()
    {
        $sql = "
            DELETE FROM KladrStreet
            WHERE parentCode NOT IN
                (SELECT id FROM KladrRegion)";
        $stmt = $this->em->getConnection()->prepare($sql);

        return $stmt->execute();
    }
}
