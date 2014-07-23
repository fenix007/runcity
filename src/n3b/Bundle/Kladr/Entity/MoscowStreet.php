<?php

namespace n3b\Bundle\Kladr\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
Class MoscowStreet
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
    private $socr;
    /**
     * @ORM\Column
     */
    private $title;
    /**
     * @ORM\Column(type="integer")
     */
    private $zip;
    /**
     * @ORM\Column(type="bigint")
     */
    private $ocatd;
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
     * Set socr
     *
     * @param string $socr
     */
    public function setSocr($socr)
    {
        $this->socr = $socr;
    }

    /**
     * Get socr
     *
     * @return string $socr
     */
    public function getSocr()
    {
        return $this->socr;
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
     * Set zip
     *
     * @param integer $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Get zip
     *
     * @return integer $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set ocatd
     *
     * @param bigint $ocatd
     */
    public function setOcatd($ocatd)
    {
        $this->ocatd = $ocatd;
    }

    /**
     * Get ocatd
     *
     * @return bigint $ocatd
     */
    public function getOcatd()
    {
        return $this->ocatd;
    }

    /**
     * Set parent
     *
     * @param n3b\Bundle\Kladr\Entity\KladrRegion $parent
     */
    public function setParent(\n3b\Bundle\Kladr\Entity\KladrRegion $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return n3b\Bundle\Kladr\Entity\KladrRegion $parent
     */
    public function getParent()
    {
        return $this->parent;
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
}