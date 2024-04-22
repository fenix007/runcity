<?php

namespace n3b\Bundle\Kladr\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
Class MoscowHouse
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="MoscowStreet")
     * @ORM\JoinColumn(name="moscow_street_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $moscowStreet;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getMoscowStreet()
    {
        return $this->moscowStreet;
    }

    public function setMoscowStreet($moscowStreet)
    {
        $this->moscowStreet = $moscowStreet;
    }
}