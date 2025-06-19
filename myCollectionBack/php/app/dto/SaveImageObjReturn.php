<?php

namespace MyCollection\app\dto;

use MyCollection\app\dto\entities\Media;

class SaveImageObjReturn
{


    private ?string $imageMode;

    private ?Media $image = null;

    private bool $isImageSaved = false;

    private ?string $filepathOnServer = null;

    private ?\Exception $exception = null;

    public function getImageMode(): string
    {
        return $this->imageMode;
    }

    public function setImageMode(string $imageMode): SaveImageObjReturn
    {
        $this->imageMode = $imageMode;
        return $this;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): SaveImageObjReturn
    {
        $this->image = $image;
        return $this;
    }

    public function isImageSaved(): bool
    {
        return $this->isImageSaved;
    }

    public function setIsImageSaved(bool $isImageSaved): SaveImageObjReturn
    {
        $this->isImageSaved = $isImageSaved;
        return $this;
    }

    public function getFilepathOnServer(): ?string
    {
        return $this->filepathOnServer;
    }

    public function setFilepathOnServer(?string $filepathOnServer): SaveImageObjReturn
    {
        $this->filepathOnServer = $filepathOnServer;
        return $this;
    }

    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    public function setException(?\Exception $exception): SaveImageObjReturn
    {
        $this->exception = $exception;
        return $this;
    }




}