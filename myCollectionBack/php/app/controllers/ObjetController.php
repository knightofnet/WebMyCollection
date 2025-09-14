<?php

namespace MyCollection\app\controllers;


use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\business\importobjetsfromcsv\ImportObjetFromCsvWorker;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\cst\TypeCategorieCst;
use MyCollection\app\dto\entities\AvoirCategorie;
use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\dto\ResponsePropsObject;
use MyCollection\app\services\CategorieServices;
use MyCollection\app\services\MediaServices;
use MyCollection\app\services\ObjetServices;
use MyCollection\app\services\ProprietaireService;
use MyCollection\app\services\Services;
use MyCollection\app\utils\AppUtils;
use MyCollection\app\utils\AuthUtils;
use MyCollection\app\utils\BddUtils;
use MyCollection\app\utils\lang\ArrayUtils;
use MyCollection\app\utils\MediaUtils;
use MyCollection\app\utils\ValidatorsUtils;

class ObjetController extends AbstractController implements IObjetController
{
    private ObjetServices $objetServices;
    private ProprietaireService $proprietaireService;

    private CategorieServices $categorieServices;

    private MediaServices $mediaServices;

    public function __construct()
    {
        $this->objetServices = Services::instance()->getObjetServices();
        $this->proprietaireService = Services::instance()->getProprietaireService();
        $this->mediaServices = Services::instance()->getMediaServices();
        $this->categorieServices = Services::instance()->getCategorieServices();
    }

    /** @noinspection PhpUnused */
    public function getAllByUserId(int $userId): ResponseObject
    {

        $currentUserId = AuthUtils::verifyTokenByCookieAndReturnProp('userId');
        if (empty($currentUserId) || intval($currentUserId) !== $userId) {
            $retObj = new ResponsePropsObject();
            $retObj->setResult(false)
                ->setErrorMsg('Vous n\'êtes pas autorisé à accéder aux objets de cet utilisateur.')
                ->setErrCode(403);
            return ResponseObject::ResultsObjectToJson($retObj);
        }

        $objets = $this->objetServices->getObjetsByIdProprietaire($userId);

        $objetsArray = AppUtils::toArrayIToArray($objets);
        $toFilterKey = [];

        foreach ($objetsArray as &$objet) {
            list($objet, $toFilterKey) = $this->fillFullObjet($objet, $toFilterKey);

        }

        $retArray = ResponseUtils::getDefaultResponseArray(true);
        $retArray['content']['data'] = $objetsArray;
        $retArray['content']['type'] = 'Objet[]';

        return ResponseObject::ResultsObjectToJson(AppUtils::toArrayIToArray($retArray, $toFilterKey));


    }


    /**
     * @param $objet
     * @param $toFilterKey
     * @return array [Objet as array, filterKeys]
     * @throws \Exception
     */
    public function fillFullObjet($objet, $inputToFilterKey): array
    {
        if ($objet instanceof Objet) {
            $objet = AppUtils::toArrayIToArray($objet->toArray());
        }

        $idObjet = $objet['Id_Objet'];

        // On ajoute les proprietaires
        $objet[Proprietaire::TABLE] = $this->proprietaireService->getProprietairesByIdObjet($idObjet);
        $toFilterKey = array_merge($inputToFilterKey, ['HashCodePin', 'Email']);

        // On ajoute les medias
        $objet[Media::TABLE] = $this->mediaServices->getMediaByIdObjet($idObjet);

        // On ajoute les categories
        $allCatsOfObjet = $this->objetServices->getCategoriesByIdObjet($idObjet);
        $objet[Categorie::TABLE] = ArrayUtils::find(
            fn($cat) => $cat->getIdTyCategorie() !== TypeCategorieCst::IdTyCatKeyword, $allCatsOfObjet
        );

        // On ajoute les mots-clés
        $objet[TypeCategorieCst::ObjetPropKeywords] = ArrayUtils::find(
            fn($cat) => $cat->getIdTyCategorie() === TypeCategorieCst::IdTyCatKeyword, $allCatsOfObjet
        );

        return array($objet, $toFilterKey);
    }

    public function updateObjet(): ResponseObject
    {
        $retArray = ResponseUtils::getDefaultResponseArray(false);
        $datas = $this->getRequest()->getBodyJson();

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['idObjet', 'data'], $datas)) {
                throw new \Exception('Les champs idObjet et data sont obligatoires.');
            }
            if (!ValidatorsUtils::isValidInt($datas['idObjet'], 1)) {
                throw new \Exception('L\'id de l\'objet est obligatoire.');
            }

            $idObjet = intval($datas['idObjet']);
            $objetExisting = $this->objetServices->getObjetById($idObjet);
            if (empty($objetExisting)) {
                throw new \Exception('L\'objet avec l\'id ' . $idObjet . ' n\'existe pas.');
            }

            $datas = $datas['data'];

            $datas = $this->validateDatasAddNewObjet($datas, ['imageMode']);

            $isObjetExistingHasChange = false;

            if ($objetExisting->getNom() !== $datas['nom']) {
                $isObjetExistingHasChange = true;
                $objetExisting->setNom($datas['nom']);
            }
            if ($objetExisting->getDescription() !== $datas['description']) {
                $isObjetExistingHasChange = true;
                $objetExisting->setDescription($datas['description']);
            }

            if ($isObjetExistingHasChange && !$this->objetServices->updateObjet($objetExisting)) {
                throw new \Exception('Erreur lors de la mise à jour de l\'objet.');
            }

            $objetExistingArray = $this->fillFullObjet($objetExisting, [])[0];


            // On met à jour les proprietaires
            // ----
            $idsPropsRaw = $datas['idProprietaire'];

            $currentProprietaireIds = array_map(fn($prop) => $prop->getIdProprietaire(), $objetExistingArray[Proprietaire::TABLE]);

            $this->updatesDiffOn($currentProprietaireIds, $idsPropsRaw,
                fn($idPropToDel) => $this->objetServices->deleteEtrePossede($objetExisting->getIdObjet(), $idPropToDel),
                fn($idPropToAdd) => $this->objetServices->addEtrePossede(new EtrePossede($objetExisting->getIdObjet(), $idPropToAdd))
            );


            // On met à jour les catégories
            // ----
            $categoriesRaw = $datas['categories'];
            $currentCategories = $objetExistingArray[Categorie::TABLE];

            $currentCategoriesIds = array_map(fn($cat) => $cat->getIdCategorie(), $currentCategories);
            $categoriesRawId = array_map(fn($cat) => $cat['Id_Categorie'], $categoriesRaw);

            $this->updatesDiffOn($currentCategoriesIds, $categoriesRawId,
                fn($idPropToDel) => $this->categorieServices->deleteAvoirCategorieByIdObjet($objetExisting->getIdObjet(), $idPropToDel),
                function ($idPropToAdd) use ($objetExisting, $categoriesRaw) {
                    $newCat = ArrayUtils::findOne(fn($nCat) => $nCat['Id_Categorie'] == $idPropToAdd, $categoriesRaw);
                    if (empty($newCat)) {
                        throw new \Exception('La catégorie avec l\'id ' . $idPropToAdd . ' n\'existe pas dans les données fournies.');
                    }
                    $this->addOrLinkCategorie($newCat, $objetExisting);
                    return true;
                }
            );


            // On met à jour les mots-clés
            // ----

            $keywordsRaw = $datas['keywords'];
            $currentKeywords = $objetExistingArray[TypeCategorieCst::ObjetPropKeywords];

            $currentKeywordsIds = array_map(fn($cat) => $cat->getIdCategorie(), $currentKeywords);
            $keywordsRawId = array_map(fn($cat) => $cat['Id_Categorie'], $keywordsRaw);

            $this->updatesDiffOn($currentKeywordsIds, $keywordsRawId,
                fn($idPropToDel): bool => $this->categorieServices->deleteAvoirCategorieByIdObjet($objetExisting->getIdObjet(), $idPropToDel),
                function ($idPropToAdd) use ($objetExisting, $keywordsRaw) {
                    $newCat = ArrayUtils::findOne(fn($nCat) => $nCat['Id_Categorie'] == $idPropToAdd, $keywordsRaw);
                    if (empty($newCat)) {
                        throw new \Exception('Le mot-clé avec l\'id ' . $idPropToAdd . ' n\'existe pas dans les données fournies.');
                    }
                    $this->addOrLinkCategorie($newCat, $objetExisting);
                    return true;
                }
            );


            BddUtils::commitTransaction();
            $retArray['result'] = true;
            $retArray['content']['data'] = true;
            $retArray['content']['type'] = 'bool';
            return ResponseObject::ResultsObjectToJson($retArray);

        } catch (\Exception $exception) {

            BddUtils::rollbackTransaction();

            $retArray['content']['message'] = 'Erreur lors de la mise à jour de l\'objet : ' . $exception->getMessage();
            return ResponseObject::ResultsObjectToJson($retArray);
        }

    }

    public function addMediaForObjet() : ResponseObject {

        $userId = AuthUtils::verifyTokenByCookieAndReturnProp('userId');

        $respObj = new ResponsePropsObject();

        $datas = json_decode($_POST['data'], JSON_OBJECT_AS_ARRAY);
        $filepathOnServer = null;

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['idObjet', 'imageMode'], $datas)) {
                throw new \Exception('Les champs idObjet et imageMode sont obligatoires.');
            }

            if (!ValidatorsUtils::isValidInt($datas['idObjet'], 1)) {
                throw new \Exception('L\'id de l\'objet est obligatoire.');
            }

            $idObjet = intval($datas['idObjet']);
            $objetExisting = $this->objetServices->getObjetById($idObjet);
            if (empty($objetExisting)) {
                throw new \Exception('L\'objet avec l\'id ' . $idObjet . ' n\'existe pas.');
            }
            $objetFillFull = $this->fillFullObjet($objetExisting, [])[0];
            if (!ArrayUtils::find(fn($prop) => $prop->getIdProprietaire() === intval($userId), $objetFillFull[Proprietaire::TABLE])) {
                throw new \Exception('Vous n\'êtes pas le propriétaire de cet objet.');
            }

            $imageModeRaw = htmlspecialchars($datas['imageMode']);

            // On prépare le média
            $saveMediaObject = MediaUtils::addMedia($imageModeRaw,
                $datas['imageUrl'] ?? null, 'file');

            if (!$saveMediaObject->isImageSaved()) {
                if ($saveMediaObject->getException()) {
                    throw $saveMediaObject->getException();
                }
                throw new \Exception('Erreur lors de l\'ajout du média : le fichier n\'a pas été sauvegardé.');
            }

            $media = $saveMediaObject->getImage();
            $media->setIdObjet($objetExisting->getIdObjet());

            if (!$this->mediaServices->addMedia($media)) {
                throw new \Exception('Erreur lors de l\'ajout du média à l\'objet.');
            }



            BddUtils::commitTransaction();
            $respObj->setResult(true)
                ->setData($media ? $media->toArray() : null)
                ->setType('Media');


        } catch (\Exception $exception) {
            BddUtils::rollbackTransaction();

            // Si un fichier a été uploadé, on le supprime
            if ($filepathOnServer && file_exists($filepathOnServer)) {
                unlink($filepathOnServer);
            }

            $respObj->setErrCode($exception->getCode())
                ->setErrorMsg($exception->getMessage());

        }

        return ResponseObject::ResultsObjectToJson($respObj->toArray());

    }

    public function getLastAddedObject(int $nbLast) : ResponseObject {
        $currentUserId = AuthUtils::verifyTokenByCookieAndReturnProp('userId', true);

        $respObj = new ResponsePropsObject();

        try {

            if ($nbLast < 0 || $nbLast > 50) {
                throw new \Exception("Out of range");
            }



            $objets = $this->objetServices->getLastAddedObject($nbLast);
            $objetsArray = AppUtils::toArrayIToArray($objets);
            $toFilterKey = [];

            foreach ($objetsArray as &$objet) {
                list($objet, $toFilterKey) = $this->fillFullObjet($objet, $toFilterKey);

            }

            $respObj->setData($objetsArray)
                ->setType('Objet[]')
                ->setResult(true);

            return ResponseObject::ResultsObjectToJson($respObj->toArray());



        } catch (\Exception $ex) {


            return ResponseObject::ResultsObjectToJson($respObj->toArray(), 500);
        }
    }

    public function getObjetNamesLike(string $namePart) : ResponseObject {
        $currentUserId = AuthUtils::verifyTokenByCookieAndReturnProp('userId', true);

        $respObj = new ResponsePropsObject();

        try {

            $namePart = trim(htmlspecialchars(urldecode($namePart)));


            $names = $this->objetServices->getObjetNamesLike($namePart);

            $respObj->setData($names)
                ->setType('String[]')
                ->setResult(true);

            return ResponseObject::ResultsObjectToJson($respObj->toArray());



        } catch (\Exception $ex) {
            $respObj->setErrCode($ex->getCode())
                ->setErrorMsg('Erreur lors de la récupération des noms d\'objets : ' . $ex->getMessage());
            return ResponseObject::ResultsObjectToJson($respObj->toArray(), 500);
        }
    }

    /**
     * TODO : finir cette méthode
     * @return ResponseObject
     */
    public function importFromCsv() : ResponseObject {

        // AuthUtils::verifyTokenByCookieAndReturnProp('userId', true);


        $retObj = new ResponsePropsObject();

        //$datas = json_decode($_POST['data'], JSON_OBJECT_AS_ARRAY);
        $filepathOnServer = null;

        global $siteConf;

        $dirRelOnServer = $siteConf->getValue('upload', 'folder');

        try {
            BddUtils::initTransaction();

            /*
             * Partie 1 - Upload du fichier CSV
             */

            if (!ValidatorsUtils::existsInArray('file', $_FILES)) {
                throw new \Exception('Le fichier est obligatoire.');
            }

            $file = $_FILES['file'];
            if (!is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Le fichier n\'a pas été uploadé correctement.');
            }
            if (!in_array($file['type'], ['text/csv', 'application/vnd.ms-excel'], true)) {
                throw new \Exception('Le type de fichier n\'est pas autorisé. Seuls les fichiers CSV sont acceptés.');
            }

            $randomFileName = uniqid('importCsv_', true) . '.csv';

            $filePathOnServer = SERVER_ROOT . '/' . $dirRelOnServer . '/' . $randomFileName;
            if (!move_uploaded_file($file['tmp_name'], $filePathOnServer)) {
                throw new \Exception('Erreur lors de l\'enregistrement du fichier.');
            }

            // Upload réussi

            /*
             * Partie 2 - Lecture du fichier CSV et ajout des objets
             */
            $importWorker = new ImportObjetFromCsvWorker($filePathOnServer);
            $importResult = $importWorker->parseFile();




            BddUtils::commitTransaction();

        } catch (\Exception $ex) {
            BddUtils::rollbackTransaction();

            $retObj->setErrCode($ex->getCode())
                ->setErrorMsg('Erreur lors de l\'initialisation de la transaction : ' . $ex->getMessage());
            return ResponseObject::ResultsObjectToJson($retObj->toArray(), 500);

        }

    }

    public function getObjetById(int $objetId): ResponseObject
    {

        $currentUserId = AuthUtils::verifyTokenByCookieAndReturnProp('userId', true);

        $retArray = ResponseUtils::getDefaultResponseArray();

        $toFilterKey = [];

        $objet = $this->objetServices->getObjetById($objetId);
        if (empty($objet)) {
            $retArray['result'] = false;
            $retArray['error']['msg'] = 'L\'objet avec l\'id ' . $objetId . ' n\'existe pas.';
        } else {


            list($objet, $toFilterKey) = $this->fillFullObjet($objet, $toFilterKey);
            $retArray['result'] = true;
            $retArray['content']['data'] = $objet;
            $retArray['content']['type'] = 'Objet';

        }

        return ResponseObject::ResultsObjectToJson(AppUtils::toArrayIToArray($retArray, $toFilterKey));


    }

    private function validateDatasAddNewObjet(array $datas, array $skipPart = []): array
    {


        ValidatorsUtils::allExistsInArray(['nom', 'description', 'idProprietaire'], $datas, true,
            'Les champs nom, description et idProprietaire sont obligatoires.');


        // Validation du nom
        $datas['nom'] = trim(htmlspecialchars($datas['nom']));
        ValidatorsUtils::isNotEmptyString($datas['nom'], true, 'Le nom de l\'objet ne peut pas être vide.');
        ValidatorsUtils::isStringLengthLessThan($datas['nom'], 255, true, 'Le nom de l\'objet ne peut pas dépasser 255 caractères.');


        // Validation de la description
        $datas['description'] = trim(htmlspecialchars($datas['description']));


        // Vérification des proprietaires
        $proprietaires = $datas['idProprietaire'];
        if (!is_array($proprietaires) || empty($proprietaires)) {
            throw new \Exception('Le champ idProprietaire doit être un tableau non vide.');
        }

        $cleanedProprietaires = [];
        foreach ($proprietaires as $idProp) {
            ValidatorsUtils::isValidInt($idProp, 1, null, true, 'L\'id du proprietaire doit être un entier positif.');
            $proprietaireObj = $this->proprietaireService->getProprietaireById($idProp);
            if (empty($proprietaireObj)) {
                throw new \Exception('Le proprietaire avec l\'id ' . $idProp . ' n\'existe pas.');
            }

            $cleanedProprietaires[] = $idProp;
        }
        $datas['idProprietaire'] = $cleanedProprietaires;


        // Validation du mode d'image
        if (!in_array('imageMode', $skipPart, true)) {

            $imageMode = $datas['imageMode'] ?? '';
            $validImageModes = ['upload', 'url', 'none'];
            if (!in_array($imageMode, $validImageModes, true)) {
                throw new \Exception('Le mode d\'image doit être "upload" ou "url".');
            }
            if ($imageMode === 'upload' && empty($_FILES) && !isset($_FILES['file'])) {
                throw new \Exception('Le mode d\'image "upload" nécessite un fichier image.');
            }
            if ($imageMode === 'url') {
                if (empty($datas['imageUrl'])) {
                    $datas['imageMode'] = 'none'; // Si aucune URL n'est fournie, on passe en mode "none"
                } else {
                    $datas['imageUrl'] = trim(htmlspecialchars($datas['imageUrl']));
                    ValidatorsUtils::uriIsValidAndRespond(
                        $datas['imageUrl'],
                        true,
                        'L\'URL de l\'image n\'est pas valide.'
                    );
                }
            }
        }

        // Validation des catégories
        if (!in_array('categories', $skipPart, true)
            && isset($datas['categories'])
            && is_array($datas['categories'])) {
            foreach ($datas['categories'] as &$dataCategorie) {
                $this->validateDatasCategorie($dataCategorie);

            }
        } else {
            $datas['categories'] = [];
        }

        // Validation des mots-clés
        if (!in_array('categories', $skipPart, true)
            && isset($datas['keywords'])
            && is_array($datas['keywords'])) {

            foreach ($datas['keywords'] as &$dataCategorie) {
                $this->validateDatasCategorie($dataCategorie);

            }
        } else {
            $datas['keywords'] = [];
        }

        return $datas;
    }

    /**
     * @param $dataCategorie
     * @return void
     */
    private function validateDatasCategorie(array &$dataCategorie): void
    {
        ValidatorsUtils::allExistsInArray(['Id_Categorie', 'Nom'], $dataCategorie, true,
            'Chaque catégorie doit contenir les champs Id_Categorie et Nom.');

        ValidatorsUtils::isValidInt($dataCategorie['Id_Categorie'], null, null, true,
            'L\'id de la catégorie doit être un entier.');

        $idCategorie = intval($dataCategorie['Id_Categorie']);
        $dataCategorie['Id_Categorie'] = $idCategorie;
        $dataCategorie['Nom'] = trim(htmlspecialchars($dataCategorie['Nom']));

        if ($idCategorie <= 0) {
            ValidatorsUtils::allExistsInArray(['Id_TyCategorie'], $dataCategorie, true,
                'Pour une nouvelle catégorie, le champ Id_TyCategorie est obligatoire.');

            ValidatorsUtils::isValidInt($dataCategorie['Id_TyCategorie'], 1, 2, true,
                'Le type de catégorie doit être un entier compris entre 1 et 2.');

            $dataCategorie['Id_TyCategorie'] = intval($dataCategorie['Id_TyCategorie']);
        }
    }

    private function updatesDiffOn(array $listIdExisting, $listIdPosted, \Closure $closureDelete, \Closure $closureAdd): void
    {

        $diffNotNeeded = ArrayUtils::diff($listIdExisting, $listIdPosted);
        foreach ($diffNotNeeded as $idToDel) {
            try {

                if (!$closureDelete($idToDel)) {
                    throw new \Exception('Erreur lors de la suppression de l\'élément avec l\'id ' . $idToDel . ' (return false).');
                }
            } catch (\Exception $e) {
                throw new \Exception('Erreur lors de la suppression de l\'élément avec l\'id ' . $idToDel . ' (exception dans la closure) : ' . $e->getMessage());
            }
        }

        $diffIsNew = ArrayUtils::diff($listIdPosted, $listIdExisting);
        foreach ($diffIsNew as $idToAdd) {
            try {
                if (!$closureAdd($idToAdd)) {
                    throw new \Exception('Erreur lors de l\'ajout de l\'élément avec l\'id ' . $idToAdd . ' (return false).');
                }
            } catch (\Exception $e) {
                throw new \Exception('Erreur lors de l\'ajout de l\'élément avec l\'id ' . $idToAdd . ' (exception dans la closure) : ' . $e->getMessage());
            }
        }

    }

    /**
     * @param $dataCategorie
     * @param Objet $newObj
     * @return void
     * @throws \Exception
     */
    private function addOrLinkCategorie($dataCategorie, Objet $newObj): void
    {

        // verifions
        $idCat = $dataCategorie['Id_Categorie'];

        $nomUnique = htmlspecialchars($dataCategorie['Nom']);
        $nomUnique = AppUtils::strToKebabCase($nomUnique, true);


        /** @var Categorie $categorieObj */
        $categorieObj = null;
        if ($idCat <= 0) {

            $idTyCat = $dataCategorie['Id_TyCategorie'];

            if ($cat = $this->categorieServices->getCategorieByNomUniqueAndType($nomUnique, $idTyCat)) {
                $categorieObj = $cat;
            }

        } else {
            $categorieObj = $this->categorieServices->getCategorieById($idCat);
        }

        if (empty($categorieObj)) {
            $categorieObj = new Categorie();
            $categorieObj->setNom($dataCategorie['Nom']);
            $categorieObj->setNomUnique($nomUnique);
            $categorieObj->setIdTyCategorie($dataCategorie['Id_TyCategorie']);
            if (!$this->categorieServices->addCategorie($categorieObj)) {
                throw new \Exception('Erreur lors de l\'ajout de la catégorie.');
            }
        }

        if (!$this->categorieServices->addAvoirCategorie(new AvoirCategorie(
            $newObj->getIdObjet(),
            $categorieObj->getIdCategorie()
        ))) {
            throw new \Exception('Erreur lors de l\'ajout de la catégorie à l\'objet.');
        }
    }

    public function deleteObjet(): ResponseObject
    {
        $retArray = ResponseUtils::getDefaultResponseArray(false);
        $datas = $this->getRequest()->getBodyJson();

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['idObjet'], $datas)) {
                throw new \Exception('Le champ idObjet est obligatoire.');
            }
            if (!ValidatorsUtils::isValidInt($datas['idObjet'], 1)) {
                throw new \Exception('L\'id de l\'objet est obligatoire.');
            }

            $idObjet = intval($datas['idObjet']);
            $objetExisting = $this->objetServices->getObjetById($idObjet);
            if (empty($objetExisting)) {
                throw new \Exception('L\'objet avec l\'id ' . $idObjet . ' n\'existe pas.');
            }

            if (!$this->objetServices->deleteObjet($objetExisting->getIdObjet())) {
                throw new \Exception('Erreur lors de la suppression de l\'objet.');
            }

            $retArray['result'] = true;
            $retArray['content']['data'] = true;
            $retArray['content']['type'] = 'bool';
            BddUtils::commitTransaction();

            return ResponseObject::ResultsObjectToJson($retArray);

        } catch (\Exception $exception) {
            BddUtils::rollbackTransaction();
            $retArray['content']['message'] = 'Erreur lors de la suppression de l\'objet : ' . $exception->getMessage();
            return ResponseObject::ResultsObjectToJson($retArray);

        }

    }

    public function deleteMediaForObjet() : ResponseObject
    {
        /** @var int $userId */
        $userId = intval(AuthUtils::verifyTokenByCookieAndReturnProp('userId', true) ?? "0");

        $retObj = new ResponsePropsObject();
        $datas = $this->getRequest()->getBodyJson();

        try {
            BddUtils::initTransaction();

            if (!ValidatorsUtils::allExistsInArray(['idMedia'],   $datas)) {
                throw new \Exception('Le champ idMedia est obligatoire.');
            }
            if (!ValidatorsUtils::isValidInt($datas['idMedia'], 1)) {
                throw new \Exception('L\'id du média est obligatoire.');
            }

            $idMedia = intval($datas['idMedia']);
            $mediaExisting = $this->mediaServices->getMediaById($idMedia);
            if (empty($mediaExisting)) {
                throw new \Exception('Le média avec l\'id ' . $idMedia . ' n\'existe pas.');
            }

            $objetExisting = $this->objetServices->getObjetById($mediaExisting->getIdObjet());
            if (empty($objetExisting)) {
                throw new \Exception('L\'objet associé au média n\'existe pas.');
            }
            $objetFillFull = $this->fillFullObjet($objetExisting, [])[0];
            if (!ArrayUtils::find(fn($prop) => $prop->getIdProprietaire() === intval($userId), $objetFillFull[Proprietaire::TABLE])) {
                throw new \Exception('Vous n\'êtes pas le propriétaire de cet objet.');
            }

            if (!$this->mediaServices->deleteMedia($mediaExisting->getIdMedia())) {
                throw new \Exception('Erreur lors de la suppression du média.');
            }

            // Si le média était le principal, on en choisit un autre comme principal
            if ($mediaExisting->isEstPrincipal()) {
                $otherMedias = $objetFillFull[Media::TABLE];
                if (!empty($otherMedias) && count($otherMedias) > 1) {
                    $otherMedias[1]->setEstPrincipal(true);
                    if (!$this->mediaServices->updateMedia($otherMedias[1])) {
                        throw new \Exception('Erreur lors de la mise à jour du média principal.');
                    }
                }
            }

            // Si le média était un fichier sur le serveur, on le supprime
            if ($mediaExisting->getType() === FormatCst::MediaTypeImage ) {
                if (!empty($mediaExisting->getUriServeur()) && file_exists(SERVER_ROOT . '/' . $mediaExisting->getUriServeur())) {
                    if (!unlink(SERVER_ROOT . '/' . $mediaExisting->getUriServeur())) {
                        throw new \Exception('Erreur lors de la suppression du fichier sur le serveur.');
                    }
                }

            }

            BddUtils::commitTransaction();
            $retObj->setResult(true)
                ->setData(true)
                ->setType('boolean');

        } catch (\Exception $ex) {
            BddUtils::rollbackTransaction();
        }

        return ResponseObject::ResultsObjectToJson($retObj->toArray());

    }

    public function addNewObjet(): ResponseObject
    {
        AuthUtils::verifyTokenByCookieAndReturnProp('userId', true);


        $retArray = ResponseUtils::getDefaultResponseArray(false);
        $datas = json_decode($_POST['data'], JSON_OBJECT_AS_ARRAY);
        $filepathOnServer = null;

        try {
            BddUtils::initTransaction();


            $datas = $this->validateDatasAddNewObjet($datas);

            $nomRaw = $datas['nom'];
            $descriptionRaw = $datas['description'];
            $idsPropsRaw = $datas['idProprietaire'];
            $categoriesRaw = $datas['categories'];
            $keywordsRaw = $datas['keywords'];
            $imageModeRaw = $datas['imageMode'];


            // verifs

            $newObj = new Objet();
            $newObj->setNom($nomRaw);
            $newObj->setDescription($descriptionRaw);
            $newObj->setDateAjout(new \DateTime('now'));

            if (!$this->objetServices->addObjet($newObj)) {
                throw new \Exception('Erreur lors de l\'ajout de l\'objet.');
            }

            // On ajoute les proprietaires
            foreach ($idsPropsRaw as $idProp) {
                if (!$this->objetServices->addEtrePossede(new EtrePossede($newObj->getIdObjet(), $idProp))) {
                    throw new \Exception('Erreur lors de l\'ajout du proprietaire à l\'objet.');
                }
            }

            // On ajoute les categories
            if (!empty($categoriesRaw) && is_array($categoriesRaw)) {
                foreach ($categoriesRaw as $dataCategorie) {
                    $this->addOrLinkCategorie($dataCategorie, $newObj);
                }
            }

            // On ajoute les mots-clés
            if (!empty($keywordsRaw) && is_array($keywordsRaw)) {
                foreach ($keywordsRaw as $dataCategorie) {
                    $this->addOrLinkCategorie($dataCategorie, $newObj);
                }
            }

            // On ajoute les medias
            if ($imageModeRaw === 'upload') {
                $mediaType = FormatCst::MediaTypeImage;
                $file = $_FILES['file'];
                if (empty($file) || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                    throw new \Exception('Le fichier n\'a pas été uploadé correctement.');
                }
                $authMimeType = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file['type'], $authMimeType, true)) {
                    throw new \Exception('Le type de fichier n\'est pas autorisé. Types autorisés : ' . implode(', ', $authMimeType));
                }

                global $siteConf;
                $filepathOnServer = SERVER_ROOT . '/' . $siteConf->getValue('upload', 'folder') . '/' . $file['name'];
                $relativePath = $siteConf->getValue('upload', 'folder') . '/' . $file['name'];
                if (!move_uploaded_file($file['tmp_name'], $filepathOnServer)) {
                    throw new \Exception('Erreur lors de l\'enregistrement du fichier.');
                }

                $media = new Media();
                $media->setIdObjet($newObj->getIdObjet());
                $media->setType($mediaType);
                $media->setUriServeur($relativePath);
                $media->setEstPrincipal(true); // On peut définir le premier média comme principal
                if (!$this->mediaServices->addMedia($media)) {
                    throw new \Exception('Erreur lors de l\'ajout du média à l\'objet.');
                }

            } elseif ($imageModeRaw === 'url') {
                $mediaType = FormatCst::MediaTypeDirectLinkImg;

                $media = new Media();
                $media->setIdObjet($newObj->getIdObjet());
                $media->setType($mediaType);
                $media->setUriServeur($datas['imageUrl']);
                $media->setEstPrincipal(true); // On peut définir le premier média comme principal

                if (!$this->mediaServices->addMedia($media)) {
                    throw new \Exception('Erreur lors de l\'ajout du média à l\'objet.');
                }
            }


            BddUtils::commitTransaction();
            $retArray['result'] = true;
            return ResponseObject::ResultsObjectToJson(AppUtils::toArrayIToArray($retArray));

        } catch (\Exception $e) {

            BddUtils::rollbackTransaction();
            if (isset($filepathOnServer) && file_exists($filepathOnServer)) {
                unlink($filepathOnServer); // Supprimer le fichier si l'upload a échoué
            }

            $retArray['content']['message'] = 'Erreur lors de l\'ajout de l\'objet : ' . $e->getMessage();
            return ResponseObject::ResultsObjectToJson($retArray);
        }


    }


}