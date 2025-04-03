<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubcontractorNotes
 *
 * @ORM\Table(name="subcontractor_notes")
 * @ORM\Entity(repositoryClass="App\Repository\SubcontractorNotesRepository")
 */
class SubcontractorNotes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=false)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var Subcontractor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subcontractor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subcontractor_id", referencedColumnName="subcontractor_id")
     * })
     */
    private $subcontractor;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Subcontractor
     */
    public function getSubcontractor()
    {
        return $this->subcontractor;
    }

    public function setSubcontractor($subcontractor)
    {
        $this->subcontractor = $subcontractor;
    }
}