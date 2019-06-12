<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExhibitRepository")
 *
 */
class Exhibit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $transcript;

    /**
     * @Gedmo\Slug(fields={"filename"})
     * @ORM\Column(type="string", length=64)
     */
    private $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $english;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $relativePath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getTranscript(): ?string
    {
        return $this->transcript;
    }

    public function setTranscript(?string $transcript): self
    {
        $this->transcript = $transcript;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    /*
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    */

    public function s3Url() {
        $url = sprintf('https://s3.amazonaws.com/%s/%s.ogg', getenv('S3_BUCKET') ?: 'museo.survos.com', $this->getCode());
        return $url;
    }
    public function getEnglish(): ?string
    {
        return $this->english;
    }

    public function setEnglish(?string $english): self
    {
        $this->english = $english;

        return $this;
    }

    public function getRelativePath(): ?string
    {
        return $this->relativePath;
    }

    public function setRelativePath(?string $relativePath): self
    {
        $this->relativePath = $relativePath;

        return $this;
    }

    public function getRp()
    {
        return ['id' => $this->getId()];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
