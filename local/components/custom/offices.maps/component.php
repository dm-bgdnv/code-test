<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (empty($arParams["IBLOCK_ID"])) return;

$arResult["PLACEMARKS"] = [];

$createMap = new \CustomArea\CreateMap($arParams["IBLOCK_ID"]);
if ($initMap = $createMap->initIblock())
{
    $elementsMap = $createMap->getRenderElements();

    foreach ($elementsMap as $element)
    {
        $arResult["PLACEMARKS"][] = [
            "name" => $element["NAME"],
            "phone" => $element["PROPERTIES"]["PHONE"]["VALUE"],
            "email" => $element["PROPERTIES"]["EMAIL"]["VALUE"],
            "city" => $element["PROPERTIES"]["CITY"]["VALUE"],
            "coordPoint" => explode(",", $element["PROPERTIES"]["COORDINATES"]["VALUE"])
        ];
    };
} else {
    foreach ($createMap->errors as $error) {
        $response["errors"] = [
            "errorText" => $error
        ];
    }
    echo json_encode($response);
}

$this->IncludeComponentTemplate();
