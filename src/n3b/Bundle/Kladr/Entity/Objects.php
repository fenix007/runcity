<?php

namespace n3b\Bundle\Kladr\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
Class Objects
{
    const TYPES_RIVER = 'river';
    
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
     * @ORM\Column(type="integer")
     */
    private $type;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $lng;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $lat;
    
    
    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    
    /**
     * Get lng
     *
     * @return float $lng
     */
    public function getLng()
    {
        return $this->lng;
    }
    
    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }
    
    /**
     * Set lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }
    
    /**
     * Set lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }
    
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
