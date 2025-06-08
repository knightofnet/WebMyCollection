<?php

namespace MyCollection\app\utils;

use MyCollection\app\dto\entities\PropsDiv;
use MyCollection\app\services\Services;

class PropertiesUtils
{
    /**
     * @param array|null $props
     * @return mixed
     * @throws \Exception
     */
    public static function validateProperties(array $props, bool $isAllowNewProperties = true): array
    {
        foreach ($props as &$prop) {

            if (!ValidatorsUtils::existsInArray('Id_Prop', $prop)) {
                throw new \Exception('Id_Prop is required in properties');
            }

            if (!ValidatorsUtils::isValidInt($prop['Id_Prop'])) {
                throw new \Exception('Invalid Id_Prop value');
            }

            $idProp = intval($prop['Id_Prop']);
            $prop['Id_Prop'] = $idProp;
            $isNewProp = false;
            if ($idProp > 0) {
                $propObj = Services::instance()->getPropertiesServices()->getPropertyById($idProp);
                if (empty($propObj)) {
                    throw new \Exception('Property with Id_Prop ' . $idProp . ' not found');
                }

                $propType = $propObj->getPropType();
            } else {

                if (!$isAllowNewProperties) {
                    throw new \Exception('New properties are not allowed');
                }

                // New property, we need to validate the type
                $isNewProp = true;

                if (!ValidatorsUtils::existsInArray('PropType', $prop)) {
                    throw new \Exception('PropType is required for new properties');
                }

                if (!ValidatorsUtils::isValidInt($prop['PropType'], 0, 2)) {
                    throw new \Exception('Invalid PropType value');
                }

                $propType = intval($prop['PropType']);
            }


            if (!ValidatorsUtils::allExistsInArray(['PropName', 'PropValue'], $prop)) {
                throw new \Exception('Invalid property format');
            }


            if ($propType === PropsDiv::PROP_TYPE_NUMBER) {
                if (!is_numeric($prop['PropValue'])) {
                    throw new \Exception('PropValue must be a number for PropType 1');
                }
            } else if ($propType === PropsDiv::PROP_TYPE_BOOLEAN) {
                if (!is_bool($prop['PropValue'])) {
                    throw new \Exception('PropValue must be a boolean for PropType 2');
                }
            }


        }
        return $props;
    }
}