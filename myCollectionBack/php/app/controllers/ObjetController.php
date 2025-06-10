<?php

namespace MyCollection\app\controllers;


use MiniPhpRest\core\AbstractController;
use MiniPhpRest\core\ResponseObject;
use MiniPhpRest\core\utils\ResponseUtils;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\cst\TypeCategorieCst;
use MyCollection\app\dto\entities\AvoirCategorie;
use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\dto\entities\EtrePossede;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\services\CategorieServices;
use MyCollection\app\services\MediaServices;
use MyCollection\app\services\ObjetServices;
use MyCollection\app\services\ProprietaireService;
use MyCollection\app\services\Services;
use MyCollection\app\utils\AppUtils;
use MyCollection\app\utils\BddUtils;
use MyCollection\app\utils\lang\ArrayUtils;
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
     * @return array
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

            $currentProprietaireIds = array_map(
                fn($prop) => $prop->getIdProprietaire(),
                $objetExistingArray[Proprietaire::TABLE]
            );
            $diffNotNeeded = ArrayUtils::diff($currentProprietaireIds, $idsPropsRaw);
            foreach ($diffNotNeeded as $idProp) {
                if (!$this->objetServices->deleteEtrePossede($objetExisting->getIdObjet(), $idProp)) {
                    throw new \Exception('Erreur lors de la suppression du proprietaire avec l\'id ' . $idProp . ' de l\'objet.');
                }
            }
            $diffIsNew = ArrayUtils::diff($idsPropsRaw, $currentProprietaireIds);
            foreach ($diffIsNew as $idProp) {
                if (!$this->objetServices->addEtrePossede(new EtrePossede($objetExisting->getIdObjet(), $idProp))) {
                    throw new \Exception('Erreur lors de l\'ajout du proprietaire avec l\'id ' . $idProp . ' à l\'objet.');
                }
            }


            // On met à jour les catégories
            // ----

            $categoriesRaw = $datas['categories'];
            $currentCategories = $objetExistingArray[Categorie::TABLE];

            $currentCategoriesIds = array_map(
                fn($cat) => $cat->getIdCategorie(),
                $currentCategories
            );
            $categoriesRawId = array_map(
                fn($cat) => $cat['Id_Categorie'],
                $categoriesRaw
            );

            $diffNotNeeded = ArrayUtils::diff($currentCategoriesIds, $categoriesRawId);
            foreach ($diffNotNeeded as $idCat) {
                if (!$this->categorieServices->deleteAvoirCategorieByIdObjet($objetExisting->getIdObjet(), $idCat)) {
                    throw new \Exception('Erreur lors de la suppression de la catégorie avec l\'id ' . $idCat . ' de l\'objet.');
                }
            }
            $diffIsNew = ArrayUtils::diff($categoriesRawId, $currentCategoriesIds);
            foreach ($diffIsNew as $idCat) {
                $newCat = ArrayUtils::findOne( fn($nCat) => $nCat['Id_Categorie'] == $idCat , $categoriesRaw);
                if (empty($newCat)) {
                    throw new \Exception('La catégorie avec l\'id ' . $idCat . ' n\'existe pas dans les données fournies.');
                }
                $this->addOrLinkCategorie($newCat, $objetExisting);
            }


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

    public function getObjetById(int $objetId): ResponseObject
    {

        // Todo vérifier userid (session ?)

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
    public function validateDatasCategorie(array &$dataCategorie): void
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

    public function addNewObjet(): ResponseObject
    {

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

    /**
     * @param $dataCategorie
     * @param Objet $newObj
     * @return void
     * @throws \Exception
     */
    public
    function addOrLinkCategorie($dataCategorie, Objet $newObj): void
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


}