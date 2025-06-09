<?php

namespace MyCollection\app\dto;

interface IToArray
{

    function toArray(): array;

    /**
     * @param array $row
     * @return void
     */
    function hydrateObjFromRow(array $row);

    function setIdObj(int $id): IToArray;
}