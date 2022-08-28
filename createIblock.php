<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, GET, DELETE");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/CreateBlock.php");

/** Список свойств для информационного блока */
$itemsProperties = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/properties.json"), true);

/** Список элементов для информационного блока */
$itemsElements = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/offices.json"), true);

$response = null;
$createIBlock = new \CustomArea\CreateIBlock("Офисы", "offices-items", "offices", $itemsProperties, $itemsElements);

if (!($result = $createIBlock->initIblock()))
{
    foreach ($createIBlock->errors as $error) {
        $response["errors"] = [
                "errorText" => $error
        ];
    }
} else {
    $response["success"] = [
        "successText" => \CustomArea\CreateIBlock::SUCCESS_IBLOCK_ADD_SUCCESS
    ];
}

header("Content-Type: application/json; charset=utf-8");
die(json_encode($response));
