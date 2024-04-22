<?php

namespace n3b\Bundle\Kladr\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\MoscowHouse;
use n3b\Bundle\Kladr\Entity\MoscowStreet;
use n3b\Bundle\Kladr\Service\Street;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportHousesCommand extends DoctrineCommand
{
    const PATTERN = 'http://mosopen.ru/street/%s';
    const START_FRAGMENT = 'Список домов и строений';
    const END_FRAGMENT = 'Улица на карте Москвы';

    private $em;

    protected function configure()
    {
        $this
            ->setName('kladr:import-houses')
            ->setDescription('Import houses from mosopen.ru')
            ->setHelp(<<<EOT
nothing here
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Started at ' . date('H:i:s') . '</info>');

        $this->em = $this->getEntityManager('default');
        $qb = $this->em->getRepository(MoscowStreet::class)->createQueryBuilder('ms');
        $qb->where('ms.mosopen_id is not null');
        $sub = $this->em->getRepository(MoscowHouse::class)->createQueryBuilder('mh');
        $sub->andWhere('mh.moscowStreet = ms.id');
        $qb->andWhere($qb->expr()->not($qb->expr()->exists($sub->getDQL())));
//        var_dump($qb->getQuery()->getSQL()); exit;
        $streets = $qb->getQuery()->getResult();

        foreach ($streets as $street) {
            $output->writeln('<info>import house for: ' . $street->getTitle() . '</info>');
            if (count($street->getHouses()) === 0) {
                $html = file_get_contents(sprintf(self::PATTERN, $street->getMosopenId()));

                $a = strpos($html, self::START_FRAGMENT);
                $b = strpos($html, self::END_FRAGMENT);
                $houses = str_replace('&nbsp;', ' ', substr($html, $a, $b - $a));

                preg_match_all('/<a href="[^>]+?>([^<]+?)</s', $houses, $matches);

                foreach ($matches[1] as $house) {
                    $moscowHouse = new MoscowHouse();
                    $moscowHouse->setMoscowStreet($street);
                    $moscowHouse->setTitle($house);
                    $this->em->persist($moscowHouse);
                }

                $this->em->flush();
            }
        }

        $output->writeln('<info>Success at ' . date('H:i:s') . '</info>');
    }
}
