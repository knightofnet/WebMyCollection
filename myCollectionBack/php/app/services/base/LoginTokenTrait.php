<?php

namespace MyCollection\app\services\base;

use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\entities\LoginToken;
use MyCollection\app\utils\BddUtils;

trait LoginTokenTrait
{

    /**
     * @param string $token
     * @return LoginToken[]
     */
    public function getAllLoginTokens(): array
    {
        return BddUtils::executeOrderAndGetMany(
            "SELECT * FROM " . LoginToken::TABLE . " ORDER BY Id_Token",
            [],
            LoginToken::class
        );
    }



    public function addLoginToken(int $userId, string $token, \DateTime $expireAt = null): bool
    {
        $loginToken = new LoginToken();
        $loginToken->setIdProprietaire($userId)
            ->setToken($token)
            ->setExpireAt($expireAt);

        return BddUtils::executeOrderInsert(
            "INSERT INTO " . LoginToken::TABLE . " (Id_Proprietaire, Token, ExpireAt) VALUES (:idProprietaire, :token, :expireAt)",
            [
                'idProprietaire' => $loginToken->getIdProprietaire(),
                'token' => $loginToken->getToken(),
                'expireAt' => $loginToken->getExpireAt() ? $loginToken->getExpireAt()->format(FormatCst::DateToBddFormat) : null,
            ],
            $loginToken
        );

    }

    public function getLoginTokenByToken(string $token): ?LoginToken
    {
        return BddUtils::executeOrderAndGetOne(
            "SELECT * FROM " . LoginToken::TABLE . " WHERE Token = :token",
            ['token' => $token],
            LoginToken::class
        );
    }

    public function markLoginTokenAsUsed(int $tokenId): bool
    {
        return BddUtils::executeOrderReturnIsRowCount(
            "UPDATE " . LoginToken::TABLE . " SET IsUsed = 1 WHERE Id = :tokenId",
            ['tokenId' => $tokenId]
        );
    }

}