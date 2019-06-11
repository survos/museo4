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
}
