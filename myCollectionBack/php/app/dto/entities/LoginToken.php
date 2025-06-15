<?php

namespace MyCollection\app\dto\entities;

use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\IToArray;

class LoginToken implements IToArray
{

    /*
     * Id Primaire 	int(11) 			Non 	Aucun(e) 		AUTO_INCREMENT 	Modifier Modifier 	Supprimer Supprimer
	2 	Id_Proprietaire 	int(10) 		UNSIGNED 	Non 	Aucun(e) 			Modifier Modifier 	Supprimer Supprimer
	3 	Token Index 	varchar(255) 	utf8mb4_unicode_ci 		Oui 	NULL 			Modifier Modifier 	Supprimer Supprimer
	4 	ExpireAt 	datetime 			Oui 	NULL 			Modifier Modifier 	Supprimer Supprimer
	5 	IsUsed 	tinyint(1) 			Oui 	0 			Modifier Modifier 	Supprimer Supprimer

     */

    public const TABLE = 'LoginTokens';

    private int $id;
    private int $idProprietaire;
    private ?string $token = null;
    private ?\DateTime $expireAt = null;

    private bool $isUsed = false;

    function toArray(): array
    {
        return [
            'Id' => $this->getId(),
            'Id_Proprietaire' => $this->getIdProprietaire(),
            'Token' => $this->getToken(),
            'ExpireAt' => $this->getExpireAt() ? $this->getExpireAt()->format(FormatCst::DateToBddFormat) : null,
            'IsUsed' => $this->isUsed()
        ];

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): LoginToken
    {
        $this->id = $id;
        return $this;
    }

    public function getIdProprietaire(): int
    {
        return $this->idProprietaire;
    }

    public function setIdProprietaire(int $idProprietaire): LoginToken
    {
        $this->idProprietaire = $idProprietaire;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): LoginToken
    {
        $this->token = $token;
        return $this;
    }

    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTime $expireAt): LoginToken
    {
        $this->expireAt = $expireAt;
        return $this;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): LoginToken
    {
        $this->isUsed = $isUsed;
        return $this;
    }

    /**
     * @inheritDoc
     */
    function hydrateObjFromRow(array $row)
    {
        $this->id = (int)$row['Id'];
        $this->idProprietaire = (int)$row['Id_Proprietaire'];
        $this->token = isset($row['Token']) ? (string)$row['Token'] : null;
        $this->expireAt = isset($row['ExpireAt']) ? \DateTime::createFromFormat(FormatCst::DateToBddFormat, $row['ExpireAt']) : null;
        $this->isUsed = isset($row['IsUsed']) ? (bool)$row['IsUsed'] : false;

        return $this;

    }

    function setIdObj(int $id): IToArray
    {
        return $this->setId($id);

    }
}