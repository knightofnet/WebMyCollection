<?php

namespace MyCollection\app\business\importobjetsfromcsv\dto;

class CsvHeader
{

    private string $headerName;
    private string $headerType;
    private bool $isRequired;

    private ?string $description = null;




    private int $index = -1;

    public function __construct(int $index, string $headerName, string $headerType='mixed', bool $isRequired = false)
    {
        $this->index = $index;
        $this->headerName = $headerName;
        $this->headerType = $headerType;
        $this->isRequired = $isRequired;
    }

    public function getHeaderName(): string
    {
        return $this->headerName;
    }


    public function getIndex(): int
    {
        return $this->index;
    }

    public function getHeaderType(): string
    {
        return $this->headerType;
    }

    public function setHeaderType(string $headerType): CsvHeader
    {
        $this->headerType = $headerType;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): CsvHeader
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): CsvHeader
    {
        $this->description = $description;
        return $this;
    }




}