<?php

namespace MyCollection\app\dto\entities;

use DateTime;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\IToArray;

class AccesToken implements IToArray
{
    public const TABLE = 'AccesToken';
    private ?int $Id_AccesToken = null;
    private string $UniqueId = '';
    private ?string $DeviceId = null;
    private DateTime $DateCreation;
    public function __construct()
    {
        $this->DateCreation = new DateTime();
    }

    public function getIdAccesToken(): ?int
    {
        return $this->Id_AccesToken;
    }

    public function setIdAccesToken(?int $Id_AccesToken): AccesToken
    {
        $this->Id_AccesToken = $Id_AccesToken;
        return $this;
    }

    public function getUniqueId(): string
    {
        return $this->UniqueId;
    }

    public function setUniqueId(string $UniqueId): AccesToken
    {
        $this->UniqueId = $UniqueId;
        return $this;
    }

    public function getDeviceId(): ?string
    {
        return $this->DeviceId;
    }

    public function setDeviceId(?string $DeviceId): AccesToken
    {
        $this->DeviceId = $DeviceId;
        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->DateCreation;
    }

    public function setDateCreation(DateTime $DateCreation): AccesToken
    {
        $this->DateCreation = $DateCreation;
        return $this;
    }



    public function toArray(): array
    {
        return [
            'Id_AccesToken' => $this->Id_AccesToken,
            'UniqueId'      => $this->UniqueId,
            'DeviceId'      => $this->DeviceId,
            'DateCreation'  => $this->DateCreation->format(FormatCst::DateToBddFormat),
        ];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_AccesToken = isset($row['Id_AccesToken']) ? (int)$row['Id_AccesToken'] : null;
        $this->UniqueId      = $row['UniqueId'] ?? $this->UniqueId;
        $this->DeviceId      = $row['DeviceId'] ?? null;
        $this->DateCreation  = isset($row['DateCreation']) && $row['DateCreation'] !== null ? new DateTime($row['DateCreation']) : new DateTime();
    }

    function setIdObj(int $id): IToArray
    {
        return $this->setIdAccesToken($id);
    }
}