<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\dto\IToArray;

class Media implements IToArray
{
    public const TABLE = 'Media';
    private ?int $Id_Media = null;
    private ?string $Type = null;
    private ?string $UriServeur = null;
    private bool $EstPrincipal = false;
    private int $Id_Objet = 0;

    public function getIdMedia(): ?int
    {
        return $this->Id_Media;
    }

    public function setIdMedia(?int $Id_Media): Media
    {
        $this->Id_Media = $Id_Media;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(?string $Type): Media
    {
        $this->Type = $Type;
        return $this;
    }

    public function getUriServeur(): ?string
    {
        return $this->UriServeur;
    }

    public function setUriServeur(?string $UriServeur): Media
    {
        $this->UriServeur = $UriServeur;
        return $this;
    }

    public function isEstPrincipal(): bool
    {
        return $this->EstPrincipal;
    }

    public function setEstPrincipal(bool $EstPrincipal): Media
    {
        $this->EstPrincipal = $EstPrincipal;
        return $this;
    }

    public function getIdObjet(): int
    {
        return $this->Id_Objet;
    }

    public function setIdObjet(int $Id_Objet): Media
    {
        $this->Id_Objet = $Id_Objet;
        return $this;
    }



    public function toArray(): array
    {
        return [
            'Id_Media'     => $this->Id_Media,
            'Type'         => $this->Type,
            'UriServeur'   => $this->UriServeur,
            'EstPrincipal' => $this->EstPrincipal,
            'Id_Objet'     => $this->Id_Objet,
        ];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Media     = isset($row['Id_Media']) ? (int)$row['Id_Media'] : null;
        $this->Type         = $row['Type'] ?? null;
        $this->UriServeur   = $row['UriServeur'] ?? null;
        $this->EstPrincipal = isset($row['EstPrincipal']) ? (bool)$row['EstPrincipal'] : $this->EstPrincipal;
        $this->Id_Objet     = isset($row['Id_Objet']) ? (int)$row['Id_Objet'] : $this->Id_Objet;
    }
}