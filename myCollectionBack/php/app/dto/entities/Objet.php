<?php

namespace MyCollection\app\dto\entities;

use DateTime;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\IToArray;

class Objet implements IToArray
{
    public const TABLE = 'Objet';
    private ?int $Id_Objet = null;
    private string $Nom = '';
    private ?string $Description = null;
    private ?DateTime $DateAcquisition = null;
    private ?string $UrlAchat = null;



    public function toArray(): array
    {
        return [
            'Id_Objet'        => $this->Id_Objet,
            'Nom'             => $this->Nom,
            'Description'     => $this->Description,
            'DateAcquisition' => $this->DateAcquisition ? $this->DateAcquisition->format(FormatCst::DateToBddFormat) : null,
            'UrlAchat'        => $this->UrlAchat,
        ];
    }

    public function getIdObjet(): ?int
    {
        return $this->Id_Objet;
    }

    public function setIdObjet(?int $Id_Objet): Objet
    {
        $this->Id_Objet = $Id_Objet;
        return $this;
    }

    public function getNom(): string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): Objet
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): Objet
    {
        $this->Description = $Description;
        return $this;
    }

    public function getDateAcquisition(): ?DateTime
    {
        return $this->DateAcquisition;
    }

    public function setDateAcquisition(?DateTime $DateAcquisition): Objet
    {
        $this->DateAcquisition = $DateAcquisition;
        return $this;
    }

    public function getUrlAchat(): ?string
    {
        return $this->UrlAchat;
    }

    public function setUrlAchat(?string $UrlAchat): Objet
    {
        $this->UrlAchat = $UrlAchat;
        return $this;
    }


    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Objet        = isset($row['Id_Objet']) ? (int)$row['Id_Objet'] : null;
        $this->Nom             = $row['Nom'] ?? $this->Nom;
        $this->Description     = $row['Description'] ?? null;
        $this->DateAcquisition = isset($row['DateAcquisition']) && $row['DateAcquisition'] !== null ? new DateTime($row['DateAcquisition']) : null;
        $this->UrlAchat        = $row['UrlAchat'] ?? null;
    }
}