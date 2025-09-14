<?php

namespace MyCollection\app\business\importobjetsfromcsv\dto;

use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\entities\Objet;

class ImportObjetFromCsvResult
{
    private bool $result = false;

    /**
     * @var Objet[]
     */
    private array $objetsParsed = [];

    /**
     * @var Media[]
     */
    private array $mediasParsed = [];

    /**
     * @var array<Categorie, int[]>
     */
    private array $categoriesParsed = [];

    /**
     * @var array<Categorie, int[]>
     */
    private array $keywordsParsed = [];


    private string $errorMsg = 'Error unknown';
    private int $errCode = -1;

    public function isResult(): bool
    {
        return $this->result;
    }

    public function setResult(bool $result): ImportObjetFromCsvResult
    {
        $this->result = $result;
        return $this;
    }

    public function &getObjetsParsed(): array
    {
        return $this->objetsParsed;
    }

    public function setObjetsParsed(array $objetsParsed): ImportObjetFromCsvResult
    {
        $this->objetsParsed = $objetsParsed;
        return $this;
    }

    public function &getMediasParsed(): array
    {
        return $this->mediasParsed;
    }

    public function setMediasParsed(array $mediasParsed): ImportObjetFromCsvResult
    {
        $this->mediasParsed = $mediasParsed;
        return $this;
    }

    public function &getCategoriesParsed(): array
    {
        return $this->categoriesParsed;
    }

    public function setCategoriesParsed(array $categoriesParsed): ImportObjetFromCsvResult
    {
        $this->categoriesParsed = $categoriesParsed;
        return $this;
    }

    public function &getKeywordsParsed(): array
    {
        return $this->keywordsParsed;
    }

    public function setKeywordsParsed(array $keywordsParsed): ImportObjetFromCsvResult
    {
        $this->keywordsParsed = $keywordsParsed;
        return $this;
    }

    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    public function setErrorMsg(string $errorMsg): ImportObjetFromCsvResult
    {
        $this->errorMsg = $errorMsg;
        return $this;
    }

    public function getErrCode(): int
    {
        return $this->errCode;
    }

    public function setErrCode(int $errCode): ImportObjetFromCsvResult
    {
        $this->errCode = $errCode;
        return $this;
    }



}