<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RequestRepository;

/**
 * @ORM\Entity(repositoryClass=RequestRepository::class)
 */
class Request
{
    public function __construct(string $address, string $ip)
    {
        $this->address = $address;
        $this->ip = $ip;
        $this->createdTs = new \DateTime('now', new \DateTimeZone('Utc'));
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=67)
     */
    public $address;

    /**
     * @ORM\Column(type="string", length=128)
     */
    public $ip;

    /**
     * @ORM\Column(type="datetime")
     */
    public $createdTs;
}
