<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карта офисов");
?>
<?
	$APPLICATION->IncludeComponent(
        "custom:offices.maps", 
        ".default", 
        array(
            "IBLOCK_ID" => 17,
            "COMPONENT_TEMPLATE" => ".default"
        ),
        false
    );
?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>