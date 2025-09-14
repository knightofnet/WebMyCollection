<?php

namespace MyCollection\app\business\importobjetsfromcsv;

use MyCollection\app\business\importobjetsfromcsv\dto\CsvHeader;
use MyCollection\app\business\importobjetsfromcsv\dto\ImportObjetFromCsvResult;
use MyCollection\app\cst\FormatCst;
use MyCollection\app\cst\TypeCategorieCst;
use MyCollection\app\dto\entities\Categorie;
use MyCollection\app\dto\entities\Media;
use MyCollection\app\dto\entities\Objet;
use MyCollection\app\dto\entities\Proprietaire;
use MyCollection\app\utils\lang\ArrayUtils;
use MyCollection\app\utils\lang\StringUtils;
use MyCollection\app\utils\ValidatorsUtils;

class ImportObjetFromCsvWorker
{

    private string $csvFilePathOnServer;


    public function __construct(string $csvFilePathOnServer)
    {
        $this->csvFilePathOnServer = $csvFilePathOnServer;
    }

    /**
     * @return ImportObjetFromCsvResult
     */
    public function parseFile(): ImportObjetFromCsvResult
    {
        $retObj = new ImportObjetFromCsvResult();
        $list = [];

        try {

            $fileContent = file_get_contents($this->csvFilePathOnServer);
            if ($fileContent === false) {
                throw new \Exception('Erreur lors de la lecture du fichier CSV.');
            }


            $lines = $this->reMergeLines($fileContent); // Recombine lines if necessary

            $headers = $this->readCsvHeaders($lines);
            if (empty($headers)) {
                throw new \Exception('Le fichier CSV ne contient pas d\'entêtes valides.');
            }

            $lineNum = 1;
            $line = "";

            while ($lineRaw = array_shift($lines)) {

                $line = trim($lineRaw);

                if (empty($line)) {
                    continue; // Ignore empty lines
                }

                $objetStruct = $this->readLineToObjetDto($line, $headers);

                if (empty($objetStruct)) {
                    throw new \Exception('La ligne ' . $lineNum . ' est vide ou mal formatée.');
                }
                $list[] = $objetStruct;

                $lineNum++;
            }


        } catch (\Exception $e) {

            $retObj->setResult(false)
                ->setErrorMsg($e->getMessage())
                ->setErrCode($e->getCode());

        }

        return $retObj;

    }

    private function reMergeLines(string $content): array
    {

        $retArray = [];


        $currentLine = '';
        $isInQuote = false;
        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];

            if ($char === '"') {
                $isInQuote = !$isInQuote; // Toggle the quote state
            } elseif ($char === "\n" && !$isInQuote) {
                // End of line, push the current line to the array
                $retArray[] = trim($currentLine);
                $currentLine = ''; // Reset for the next line
                continue; // Skip the newline character
            }

            $currentLine .= $char; // Ajouter le caractère à la ligne en cours



        }


        return $retArray;

    }

    /**
     * @return CsvHeader[]
     * @throws \Exception
     */
    private function readCsvHeaders(&$lines): array
    {


        // Validation des entêtes
        $header = str_getcsv(array_shift($lines), ';');
        if (empty($header)) {
            throw new \Exception('Le fichier CSV est vide ou mal formaté.');
        }
        $header = array_map('trim', $header); // Nettoyage des espaces

        $fields = [
            'nom' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Nom de l\'objet'
            ],
            'description' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Description de l\'objet'
            ],
            'idProprietaire' => [
                'type' => 'int|int[]',
                'required' => true,
                'description' => 'ID du propriétaire de l\'objet'
            ],
            'dateAcquisition' => [
                'type' => 'date',
                'required' => false,
                'description' => 'Date d\'acquisition de l\'objet (format : DD/MM/YYYY)'
            ],
            'urlAchat' => [
                'type' => 'string',
                'required' => false,
                'description' => 'URL d\'achat de l\'objet'
            ],
            'urlImage' => [
                'type' => 'string',
                'required' => false,
                'description' => 'URL de l\'image de l\'objet (peut être une image encodée en base64 ou un lien direct vers une image)'
            ],
            'categorie' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Nom de la catégorie de l\'objet'
            ],
            'motclef' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Mot-clé associé à l\'objet'
            ],
        ];


        $requiredFields = array_keys(ArrayUtils::find(fn(array $field) => $field['required'] === true, $fields));
        if (!ValidatorsUtils::allExistsInArray($requiredFields, $header)) {
            throw new \Exception('Le fichier CSV doit contenir les champs : ' . implode(', ', $requiredFields));
        }

        $headers = [];
        $categoryIx = 1;
        $motClefIx = 1;

        foreach ($header as $index => $value) {

            $colProperty = null;
            $colName = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            $colOriginalName = $colName;

            $categoryName = 'categorie' . $categoryIx;
            $motClefName = 'motclef' . $motClefIx;
            if ($colName === $categoryName) {
                $colProperty = $fields['categorie'];
                $categoryIx++;
            } elseif ($colName === $motClefName) {
                $colProperty = $fields['motclef'];
                $motClefIx++;
            } elseif (isset($fields[$colName])) {
                $colProperty = $fields[$colName];
            }

            if ($colProperty != null) {

                $headerObj = new CsvHeader($index, $colName);
                if (ArrayUtils::any(fn(CsvHeader $csvHeader) => $csvHeader->getHeaderName() === $headerObj->getHeaderName(), $headers)) {
                    throw new \Exception('Le fichier CSV contient des entêtes en double : ' . $headerObj->getHeaderName());
                }
                $headerObj->setHeaderType($colProperty['type'])
                    ->setIsRequired($colProperty['required'])
                    ->setDescription($colProperty['description']);

                $headers[] = $headerObj;

            }


        }

        return $headers;


    }

    /**
     * @param string $line
     * @param CsvHeader[] $headers
     * @return array
     */
    private function readLineToObjetDto(string $line, array $headers): array
    {
        $idObjet = -1;

        $data = str_getcsv($line, ';');
        if (empty($data)) {
            return []; // Ligne vide ou mal formatée
        }

        if (count($data) !== count($headers)) {
            throw new \Exception('Le nombre de colonnes dans la ligne ne correspond pas au nombre d\'entêtes.');
        }

        $objetStruct = $this->getDataObjetStructure();
        $objetStruct[Objet::class]->setIdObjet($idObjet--);

        foreach ($headers as $index => $header) {
            $value = trim($data[$index]);
            if ($value === '' && !$header->isRequired()) {
                continue; // Ignore les valeurs vides si le champ n'est pas requis
            }

            switch ($header->getHeaderName()) {
                case 'nom':
                    $objetStruct[Objet::class]->setNom($value);
                    break;
                case 'description':
                    $objetStruct[Objet::class]->setDescription($value);
                    break;
                case 'idProprietaire':
                    if (ValidatorsUtils::isValidInt($value)) {
                        $objetStruct[Proprietaire::class][] = (int)$value;
                    } else {
                        throw new \Exception('ID du propriétaire invalide : ' . $value);
                    }
                    break;
                case 'dateAcquisition':
                    if (!empty($value)) {
                        $date = \DateTime::createFromFormat('d/m/Y', $value);
                        if ($date === false) {
                            throw new \Exception('Date d\'acquisition invalide : ' . $value);
                        }
                        $objetStruct[Objet::class]->setDateAcquisition($date);
                    }
                    break;
                case 'urlAchat':
                    $objetStruct[Objet::class]->setUrlAchat($value);
                    break;
                case 'urlImage':
                    if (!empty($value)) {
                        $media = new Media();
                        $media->setType(FormatCst::MediaTypeDirectLinkImg);
                        $media->setUriServeur($value);
                        $objetStruct[Media::class] = $media;
                    }
                    break;


            }

            if (StringUtils::str_starts_with($header->getHeaderName(), 'categorie')) {
                if (!empty($value)) {
                    $categorie = new Categorie();
                    $categorie->setNom($value);
                    $objetStruct[Categorie::class][] = $categorie;
                }
            } else if (StringUtils::str_starts_with($header->getHeaderName(), 'motclef')) {
                if (!empty($value)) {
                    $categorie = new Categorie();
                    $categorie->setNom($value);
                    $objetStruct[TypeCategorieCst::ObjetPropKeywords][] = $categorie;
                }
            }

        }

        return $objetStruct;

    }

    private function getDataObjetStructure() : array
    {

        return [
            Objet::class => new Objet(),
            Media::class => new Media(),
            Categorie::class => [],
            TypeCategorieCst::ObjetPropKeywords => [],
            Proprietaire::class => []
        ];


    }

}