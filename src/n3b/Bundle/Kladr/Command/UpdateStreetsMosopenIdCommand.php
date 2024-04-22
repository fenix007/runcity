<?php

namespace n3b\Bundle\Kladr\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\MoscowStreet;
use n3b\Bundle\Kladr\Service\Street;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStreetsMosopenIdCommand extends DoctrineCommand
{
    const PATTERN = 'http://mosopen.ru/streets/letter/%s';
    const START_FRAGMENT = 'Список улиц, расположенных на территории города Москвы, название которых начинается ';
    const END_FRAGMENT = 'Типы московских улиц';

    private $em;

    protected function configure()
    {
        $this
            ->setName('kladr:update-streets')
            ->setDescription('Update streets from mosopen.ru')
            ->setHelp(<<<EOT
nothing here
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Started at ' . date('H:i:s') . '</info>');

        $this->em = $this->getEntityManager('default');

        for ($j = 1; $j <= 33; $j++) {
            $html = file_get_contents(sprintf(self::PATTERN, $j));

            $a = strpos($html, self::START_FRAGMENT);
            $b = strpos($html, self::END_FRAGMENT);
            $streets = substr($html, $a, $b - $a);

            preg_match_all('/<a href="http:\/\/mosopen.ru\/street\/(\d+)?">([^"]+?)</s', $streets, $matches);
            foreach ($matches[2] as $i => $name) {
                $name = str_replace(',', ' ', $name);
                $street = Street::parseStreetName($name);
//                var_dump($street['title']); exit;
                /** @var MoscowStreet $res */
                $moscowStreet = $this->em->getRepository(MoscowStreet::class)->findOneBy([
                    'title' => $street['title'],
                    'socr' => $street['socr']
                ]);
                if ($moscowStreet) {
                    $moscowStreet->setMosopenId($matches[1][$i]);
                    $this->em->persist($moscowStreet);
                } else {
                    $output->writeln('<error>not found ' . $name . '</error>');
                }
            }
            $this->em->flush();
        }

        $output->writeln('<info>Success at ' . date('H:i:s') . '</info>');
    }
}
