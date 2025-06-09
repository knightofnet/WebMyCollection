<?php

namespace Enigmas\app\dto\entities;

use DateTime;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\IToArray;

class DonnerDroitAcces implements IToArray
{
    public const TABLE = 'DonnerDroitAcces';
    private int $Id_Objet = 0;
    private int $Id_Proprietaire = 0;
    private int $Id_AccesToken = 0;
    private DateTime $DateDeb;
    private ?DateTime $DateFin = null;
    private int $NiveauDetail = 0;
    public function __construct()
    {
        $this->DateDeb = new DateTime();
    }

    public function getIdObjet(): int
    {
        return $this->Id_Objet;
    }

    public function setIdObjet(int $Id_Objet): DonnerDroitAcces
    {
        $this->Id_Objet = $Id_Objet;
        return $this;
    }

    public function getIdProprietaire(): int
    {
        return $this->Id_Proprietaire;
    }

    public function setIdProprietaire(int $Id_Proprietaire): DonnerDroitAcces
    {
        $this->Id_Proprietaire = $Id_Proprietaire;
        return $this;
    }

    public function getIdAccesToken(): int
    {
        return $this->Id_AccesToken;
    }

    public function setIdAccesToken(int $Id_AccesToken): DonnerDroitAcces
    {
        $this->Id_AccesToken = $Id_AccesToken;
        return $this;
    }

    public function getDateDeb(): DateTime
    {
        return $this->DateDeb;
    }

    public function setDateDeb(DateTime $DateDeb): DonnerDroitAcces
    {
        $this->DateDeb = $DateDeb;
        return $this;
    }

    public function getDateFin(): ?DateTime
    {
        return $this->DateFin;
    }

    public function setDateFin(?DateTime $DateFin): DonnerDroitAcces
    {
        $this->DateFin = $DateFin;
        return $this;
    }

    public function getNiveauDetail(): int
    {
        return $this->NiveauDetail;
    }

    public function setNiveauDetail(int $NiveauDetail): DonnerDroitAcces
    {
        $this->NiveauDetail = $NiveauDetail;
        return $this;
    }



    public function toArray(): array
    {
        return [
            'Id_Objet'        => $this->Id_Objet,
            'Id_Proprietaire' => $this->Id_Proprietaire,
            'Id_AccesToken'   => $this->Id_AccesToken,
            'DateDeb'         => $this->DateDeb->format(FormatCst::DateToBddFormat),
            'DateFin'         => $this->DateFin ? $this->DateFin->format(FormatCst::DateToBddFormat) : null,
            'NiveauDetail'    => $this->NiveauDetail,
        ];
    }
    public function hydrateObjFromRow(array $row): void
    {
        $this->Id_Objet        = isset($row['Id_Objet']) ? (int)$row['Id_Objet'] : $this->Id_Objet;
        $this->Id_Proprietaire = isset($row['Id_Proprietaire']) ? (int)$row['Id_Proprietaire'] : $this->Id_Proprietaire;
        $this->Id_AccesToken   = isset($row['Id_AccesToken']) ? (int)$row['Id_AccesToken'] : $this->Id_AccesToken;
        $this->DateDeb         = isset($row['DateDeb']) && $row['DateDeb'] !== null ? new DateTime($row['DateDeb']) : $this->DateDeb;
        $this->DateFin         = isset($row['DateFin']) && $row['DateFin'] !== null ? new DateTime($row['DateFin']) : null;
        $this->NiveauDetail    = isset($row['NiveauDetail']) ? (int)$row['NiveauDetail'] : $this->NiveauDetail;
    }

    function setIdObj(int $id): IToArray
    {
        return $this;
    }
}