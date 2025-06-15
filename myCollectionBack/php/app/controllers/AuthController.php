<?php

namespace MyCollection\app\controllers;

use DateTime;
use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MyCollection\app\dto\ResponsePropsObject;
use MyCollection\app\services\ProprietaireService;
use MyCollection\app\utils\AuthUtils;
use MyCollection\app\utils\BddUtils;
use MyCollection\app\utils\MailUtils;
use MyCollection\app\utils\SiteIniFile;
use MyCollection\app\utils\ValidatorsUtils;

class AuthController extends AbstractController
{

    private ProprietaireService $proprietaireService;

    public function __construct()
    {
        $this->proprietaireService = new ProprietaireService();
    }


    public function isAuthenticated(): ResponseObject
    {
        $retObj = new ResponsePropsObject();
        $userId = AuthUtils::verifyTokenByCookieAndReturnProp('userId', false);
        $returnBool = false;
        if ($userId) {
            $returnBool = true;
        }
        $retObj->setResult(true)
            ->setData($returnBool)
            ->setType('boolean');

        return ResponseObject::ResultsObjectToJson($retObj->toArray(), $returnBool ? 200 : 401);

    }


    public function login(): ResponseObject
    {
        $data = $this->getRequest()->getBodyJson();

        $retObj = new ResponsePropsObject();

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['username', 'pin'], $data)) {
                throw new \Exception("fields 'username' and 'pin' are required", 400);
            }

            $propName = htmlspecialchars($data['username'] ?? '');
            $pin = htmlspecialchars($data['pin'] ?? '');

            if (empty($propName) || empty($pin)) {
                throw new \Exception("fields 'username' and 'pin' must not be empty", 400);
            }

            if (!ValidatorsUtils::isValidInt($pin)) {
                throw new \Exception("field 'pin' must be a valid integer", 400);
            }

            $proprietaire = $this->proprietaireService->getProprietaireByName($propName);
            if (!$proprietaire) {
                throw new \Exception("Proprietaire with name '$propName' not found", 404);
            }

            if (!password_verify($pin, $proprietaire->getHashCodePin())) {
                throw new \Exception("Invalid pin for Proprietaire '$propName'", 401);
            }

            // création token unique
            $token = AuthUtils::generateNewToken();
            $expires_at = new DateTime('+15 minutes');

            $this->proprietaireService->addLoginToken($proprietaire->getIdProprietaire(), $token, $expires_at);


            $subject = 'MyCollection - Nouvelle connexion';
            $body = 'Bonjour ' . $proprietaire->getNom() . ',<br><br>' .
                'Une nouvelle connexion a été détectée sur votre compte Knightofnet:MyCollection !' . "<br><br>" .
                'Cliquez sur le lien ci-dessous pour vous connecter : ' . "<br>" .
                '<a href="' . SiteIniFile::instance()->getValue('site', 'url') . 'login/' . $token . '">Poursuivre la connexion</a>' . "<br>" .
                'Si ce n\'est pas vous, ignorez ce message.' . "<br><br>" .
                'Aryx' . "<br>";

            MailUtils::sendMail($proprietaire->getEmail(), $subject, $body);

            $retObj->setResult(true)
                ->setData(true)
                ->setType('boolean');

            BddUtils::commitTransaction();

        } catch (\Exception $exception) {

            BddUtils::rollbackTransaction();

            $retObj->setErrCode($exception->getCode())
                ->setErrorMsg($exception->getMessage());

        }

        return ResponseObject::ResultsObjectToJson($retObj->toArray());

    }

    public function validateToken(): ResponseObject
    {

        $data = $this->getRequest()->getBodyJson();
        $retObj = new ResponsePropsObject();

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['token'], $data)) {
                throw new \Exception("field 'token' is required", 400);
            }

            $token = htmlspecialchars($data['token'] ?? '');
            if (empty($token)) {
                throw new \Exception("field 'token' must not be empty", 400);
            }

            $tokenObj = $this->proprietaireService->getLoginTokenByToken($token);
            if (!$tokenObj || $tokenObj->isUsed() || $tokenObj->getExpireAt() < new DateTime()) {
                throw new \Exception("Invalid token", 401);
            }

            if (!$this->proprietaireService->markLoginTokenAsUsed($tokenObj->getId())) {
                throw new \Exception("Failed to mark token as used", 500);
            }


            $payload = [
                'userId' => $tokenObj->getIdProprietaire(),
                'exp' => time() + (30 * 24 * 60 * 60)
            ];
            $jwt = AuthUtils::encodeJwtPayload($payload);
            AuthUtils::setAuthCookie($jwt);


            $retObj->setResult(true)
                ->setData(true)
                ->setType('boolean');

            BddUtils::commitTransaction();

        } catch (\Exception $exception) {

            BddUtils::rollbackTransaction();

            $retObj->setErrCode($exception->getCode())
                ->setErrorMsg($exception->getMessage());

        }

        return ResponseObject::ResultsObjectToJson(
            $retObj->toArray(),
            $retObj->getErrCode() == 1 ? 200 : $retObj->getErrCode()
        );

    }
}