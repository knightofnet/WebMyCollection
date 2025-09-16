<?php

namespace MyCollection\app\utils;

use MyCollection\app\cst\FormatCst;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\SaveImageObjReturn;
use MyCollection\app\utils\lang\StringUtils;

class MediaUtils
{
    /**
     * @var string[]
     */
    public static array $validImageModes = ['upload', 'url', 'none'];

    public static array $authMimeTypes = [
        'image/jpeg' => [
            'ext' => '.jpg',
        ],
        'image/png' => [
            'ext' => '.png',
        ],
        'image/gif' => [
            'ext' => '.gif',
        ],
        'image/webp' => [
            'ext' => '.webp',
        ],
    ];

    public static function addMedia(string $imageMode, string $imageLink = null, string $keyFileInFILE = 'file'): SaveImageObjReturn
    {

        self::validateImageMode($imageMode);

        $objReturn = new SaveImageObjReturn();
        $objReturn->setImageMode($imageMode);

        global $siteConf;

        try {

            /** @var ?Media $media */
            $media = null;

            $folderOnServer = $siteConf->getValue('upload', 'folder');
            $allowRemoteDownload = $siteConf->getValue('upload', 'allowRemoteDownload', false);
            $allowSaveBase64 = $siteConf->getValue('upload', 'allowSaveBase64', false);

            if ($imageMode === 'upload') {

                $file = $_FILES[$keyFileInFILE];

                // on réalise le traitement de l'upload
                $media = self::handleUploadMode($file, $siteConf->getValue('upload', 'folder'));

            } elseif ($imageMode === 'url') {

                // on réalise le traitement de l'url
                $media = self::handleUrlMode($imageLink, $folderOnServer, $allowSaveBase64, $allowRemoteDownload);

            }

            if ($media) {
                $objReturn->setImage($media);
                $objReturn->setIsImageSaved(true);
            }


        } catch (\Exception $e) {

            if ($objReturn->getFilepathOnServer() != null && file_exists($objReturn->getFilepathOnServer())) {
                unlink($objReturn->getFilepathOnServer());
            }

            $objReturn->setIsImageSaved(false);
            $objReturn->setImage(null);
            $objReturn->setFilepathOnServer(null);

            $objReturn->setException($e);

        }

        return $objReturn;

    }

    /**
     * @param string $imageMode
     * @return void
     */
    public static function validateImageMode(string $imageMode): void
    {
        if (!in_array($imageMode, self::$validImageModes, true)) {
            throw new \InvalidArgumentException('Mode d\'image invalide. Modes valides : ' . implode(', ', self::$validImageModes));
        }
    }

    /**
     * @param array $file contenu de $_FILES['file']
     * @param string $uploadFolder dossier de destination pour l'upload sur le serveur
     * @return Media
     * @throws \Exception
     */
    private static function handleUploadMode(array $file, string $uploadFolder): Media
    {
        if (empty($file) || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Le fichier n\'a pas été uploadé correctement.');
        }

        if (!key_exists($file['type'], self::$authMimeTypes)) {
            throw new \Exception('Le type de fichier n\'est pas autorisé. Types autorisés : ' . implode(', ', array_keys(self::$authMimeTypes)));
        }

        $imageType = self::$authMimeTypes[$file['type']]['ext'] ?? null;
        if ($imageType === null) {
            throw new \Exception('Le type de l\'image n\'est pas autorisé.');
        }
        $relativePath = self::getUniqueFileFullpath($uploadFolder, ltrim($imageType, '.'));
        $filePath = SERVER_ROOT . '/' . $relativePath;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Erreur lors de l\'enregistrement du fichier.');
        }

        $media = new Media();
        $media->setType(FormatCst::MediaTypeImage);
        $media->setUriServeur($relativePath);
        $media->setEstPrincipal(false);

        return $media;
    }

    private static function handleUrlMode(string $imageLink, string $uploadFolder, bool $allowSaveBase64, bool $allowRemoteDownload): Media
    {
        $media = new Media();
        $saveUrl = true;

        // Si le lien est une image encodée en base64
        // et que l'option est activée, on la sauvegarde
        if ($allowSaveBase64 && StringUtils::str_starts_with($imageLink, 'data:image/')) {

            $imageData = explode(',', $imageLink);
            if (count($imageData) !== 2) {
                throw new \Exception('Le lien de l\'image n\'est pas valide.');
            }
            $imageType = strtolower(str_replace(['data:image/', ';base64'], '', $imageData[0]));
            $imageContent = base64_decode($imageData[1]);
            if ($imageContent === false) {
                throw new \Exception('Le contenu de l\'image n\'est pas valide.');
            }

            list($relativePath, $serverPath) = self::fileSaveContent($uploadFolder, $imageType, $imageContent);
            $media->setType(FormatCst::MediaTypeImage);
            $media->setUriServeur($relativePath);

            $saveUrl = false; // On a réussi à enregistrer l'image, pas besoin de sauvegarder le lien

        }
        // Si le lien est un lien direct vers une image
        // et que l'option est activée, on la télécharge
        elseif ($allowRemoteDownload
            && (StringUtils::str_starts_with($imageLink, 'http://')
                || StringUtils::str_starts_with($imageLink, 'https://'))
        ) {

            $headers = get_headers($imageLink, 1);
            if ($headers === false || !isset($headers['Content-Type']) || !preg_match('/^image\/(jpeg|png|gif|webp)$/', $headers['Content-Type'])) {
                throw new \Exception('Le lien de l\'image n\'est pas valide ou ne pointe pas vers une image.');
            }

            $imageType = self::$authMimeTypes[$headers['Content-Type']]['ext'] ?? null;
            if ($imageType === null) {
                throw new \Exception('Le type de l\'image n\'est pas autorisé.');
            }

            try {
                $imageContent = file_get_contents($imageLink);
                if ($imageContent !== false) {
                    list($relativePath, $serverPath) = self::fileSaveContent($uploadFolder, ltrim($imageType, '.'), $imageContent);
                    $media->setType(FormatCst::MediaTypeImage);
                    $media->setUriServeur($relativePath);

                    $saveUrl = false;
                }

            } catch (\Exception $e) {

            }

            if ($saveUrl) {
                $media->setType(FormatCst::MediaTypeDirectLinkImg);
                $media->setUriServeur($imageLink);
            }

        } else {
            throw new \Exception('Le lien de l\'image n\'est pas valide.');

        }

        $media->setEstPrincipal(false);
        return $media;
    }

    /**
     * @param string $serveurUploadFolder
     * @param string $imageType
     * @param mixed $imageContent
     * @return string[] : [relativePath, serveurImagePath]
     * @throws \Exception
     */
    public static function fileSaveContent(string $serveurUploadFolder, string $imageType, $imageContent): array
    {
        $relativePath = self::getUniqueFileFullpath($serveurUploadFolder, $imageType);
        $serveurImagePath = SERVER_ROOT . '/' . $relativePath;
        if (file_put_contents($serveurImagePath, $imageContent) === false) {
            throw new \Exception('Erreur lors de l\'enregistrement de l\'image.');
        }
        return array($relativePath, $serveurImagePath);
    }

    /**
     * @param string $serveurUploadFolder
     * @param string $imageType
     * @return string
     */
    public static function getUniqueFileFullpath(string $serveurUploadFolder, string $imageType): string
    {
        return $serveurUploadFolder . '/' . uniqid('image_', true) . '.' . $imageType;
    }

}