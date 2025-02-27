<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvoiceItem
 *
 * @ORM\Table(name="invoice_item")
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceItemRepository")
 */
class InvoiceItem
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
     * @var float
     *
     * @ORM\Column(name="quantity_from_previous", type="float", nullable=false)
     */
    private $quantityFromPrevious;

    /**
     * @var float
     *
     * @ORM\Column(name="unpaid_from_previous", type="float", nullable=false)
     */
    private $unpaidFromPrevious;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="float", nullable=false)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_qty", type="float", nullable=false)
     */
    private $paidQty;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_amount", type="float", nullable=false)
     */
    private $paidAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_amount_total", type="float", nullable=false)
     */
    private $paidAmountTotal;

    /**
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_id", referencedColumnName="invoice_id")
     * })
     */
    private $invoice;

    /**
     * @var ProjectItem
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProjectItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_item_id", referencedColumnName="id")
     * })
     */
    private $projectItem;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
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
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return ProjectItem
     */
    public function getProjectItem()
    {
        return $this->projectItem;
    }

    public function setProjectItem($projectItem)
    {
        $this->projectItem = $projectItem;
    }

    public function getQuantityFromPrevious()
    {
        return $this->quantityFromPrevious;
    }

    public function setQuantityFromPrevious($quantityFromPrevious)
    {
        $this->quantityFromPrevious = $quantityFromPrevious;
    }

    public function getPaidQty()
    {
        return $this->paidQty;
    }

    public function setPaidQty($paidQty)
    {
        $this->paidQty = $paidQty;
    }

    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
    }

    public function getPaidAmountTotal()
    {
        return $this->paidAmountTotal;
    }

    public function setPaidAmountTotal($paidAmountTotal)
    {
        $this->paidAmountTotal = $paidAmountTotal;
    }

    public function getUnpaidFromPrevious()
    {
        return $this->unpaidFromPrevious;
    }

    public function setUnpaidFromPrevious($unpaidFromPrevious)
    {
        $this->unpaidFromPrevious = $unpaidFromPrevious;
    }

}