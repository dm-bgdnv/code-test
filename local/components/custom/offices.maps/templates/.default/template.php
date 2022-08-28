<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

use \Bitrix\Main\UI\Extension,
    \Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs("https://api-maps.yandex.ru/2.1/?lang=ru_RU");
Extension::load("ui.bootstrap4");
?>
<? if (!empty($arResult["PLACEMARKS"])): ?>
    <div class="container">
        <div class="row">
            <div class="col-gl-12">
                <h1><?=$APPLICATION->ShowTitle();?></h1>
            </div>
            <div class="col-gl-12">
                <div id="map" style="width:100%;height:480px"></div>
            </div>
        </div>
    </div>
    <script>
        ymaps.ready(init);
        var myMap,
            placemarks = <?=CUtil::PhpToJSObject($arResult["PLACEMARKS"]);?>;

        function init() {
            myMap = new ymaps.Map("map", {
                center: [56.412730, 37.260588],
                zoom: 4
            });

            for (var i in placemarks) {
                if (placemarks.hasOwnProperty(i)) {
                    var placemark = new ymaps.Placemark(placemarks[i].coordPoint, {
                        balloonContentHeader: "<strong>" + placemarks[i].name + "</strong>",
                        balloonContentBody: placemarks[i].phone + ", " + placemarks[i].email,
                        balloonContentFooter: placemarks[i].city
                    },{
                        preset: "islands#dotIcon",
                        iconColor: "#3b5998"
                    });
                    myMap.geoObjects.add(placemark);
                }
            }
        }
    </script>
<? endif; ?>
