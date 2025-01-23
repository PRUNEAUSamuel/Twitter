<?php

namespace App\Entity;

use App\Repository\LikesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikesRepository::class)]
class Likes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?users $user = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    private ?Tweets $tweet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?users
    {
        return $this->user;
    }

    public function setUser(?users $user): static
    {
        $this->user = $user;

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
