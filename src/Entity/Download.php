<?php

namespace App\Entity;

use App\Repository\DownloadRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity(repositoryClass=DownloadRepository::class)
 */
class Download
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="downloads")
     * @ORM\JoinColumn(nullable=false)
     * @JoinColumn(
     *      name="rapport_id",
     *      referencedColumnName="id",
     *      onDelete="CASCADE",
     *      nullable=false
     * )
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Rapport::class, inversedBy="downloads")
     * @ORM\JoinColumn(nullable=false)
     * @JoinColumn(
     *      name="rapport_id",
     *      referencedColumnName="id",
     *      onDelete="CASCADE",
     *      nullable=false
     * )
     */
    private $rapport;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRapport(): ?rapport
    {
        return $this->rapport;
    }

    public function setRapport(?Rapport $rapport): self
    {
        $this->rapport = $rapport;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }
}
