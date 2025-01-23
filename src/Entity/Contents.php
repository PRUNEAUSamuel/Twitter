<?php

namespace App\Entity;

use App\Repository\ContentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentsRepository::class)]
class Contents
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Tweets $tweet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getTweet(): ?Tweets
    {
        return $this->tweet;
    }

    public function setTweet(?Tweets $tweet): static
    {
        $this->tweet = $tweet;

        return $this;
    }
}
