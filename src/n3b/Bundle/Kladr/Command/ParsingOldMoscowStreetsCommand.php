<?php

namespace n3b\Bundle\Kladr\Command;

use Doctrine\ORM\EntityManager;
use Goutte\Client;
use n3b\Bundle\Kladr\Entity\MoscowStreet;
use n3b\Bundle\Kladr\Service\Street;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use n3b\Bundle\Kladr\Entity\KladrRegion;
use Doctrine\ORM\Query\ResultSetMapping;

class ParsingOldMoscowStreetsCommand extends DoctrineCommand
{
    /** @var  EntityManager */
    protected $em;
    
    protected function configure()
    {
        $this
            ->setName('kladr:parsing:old_streets')
            ->setDescription('Parsing old streets name')
            ->setHelp(<<<EOT
nothing here
EOT
        );
    }
    
    const   SITE_URL = 'http://www.allo499.ru/info/128/433/part-%s/';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Start parsing</info>');
        
        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        
        $client = new Client();
        for ($page = 1; $page < 5; $page++) {
            $url = sprintf(self::SITE_URL, $page);
            $html = file_get_contents($url);
            $html = preg_replace('/<!--.*?-->/s', '', $html);

            $crawler = new Crawler($html);
            
            $this->parsePage($crawler);
        }
        
        $output->writeln('<info>Success at ' . date('H:i:s') . '</info>');
    }
    
    /**
     * @param Crawler $crawler
     */
    private function parsePage($crawler)
    {
        $moscowStreetRepo = $this->getContainer()->get('doctrine')
            ->getRepository('n3bKladrBundle:MoscowStreet');
        

        $crawler->filter('.content table tr')->each(function ($node) use ($moscowStreetRepo) {
            /** @var Crawler $node */
            $oldName = trim($node->filter('td')->eq(0)->text(), ' ');
            $newName = trim($node->filter('td')->eq(1)->text(), ' ');

            if ($oldName && $oldName !== "Старое название") {
                $streetInfo = Street::parseStreetName($newName);
                
                /** @var MoscowStreet $street */
                $street = $moscowStreetRepo->findOneBy(['title' => $streetInfo['title']]);
                if ($street) {
                    $street->setTitle($street->getTitle() . " ({$oldName})");
                    $this->em->persist($street);
                    $this->em->flush();
                }
            }
        });

    }
}
