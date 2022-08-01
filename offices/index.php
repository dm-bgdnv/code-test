<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карта офисов");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/CreateMap.php");

use \Bitrix\Main\UI\Extension,
    \Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1/?lang=ru_RU');
Extension::load('ui.bootstrap4');

$createMap = new \CustomArea\CreateMap("offices-items");
if ($initMap = $createMap->initIblock())
{
    $jsPlacemarks = [];
    $elementsMap = $createMap->getRenderElements();

    foreach ($elementsMap as $element)
    {
        $jsPlacemarks[] = [
            'name' => $element['NAME'],
            'phone' => $element['PROPERTIES']['PHONE']['VALUE'],
            'email' => $element['PROPERTIES']['EMAIL']['VALUE'],
            'city' => $element['PROPERTIES']['CITY']['VALUE'],
            'coordPoint' => explode(',', $element['PROPERTIES']['COORDINATES']['VALUE'])
        ];
    };
} else {
    foreach ($createMap->errors as $error) {
        $response['errors'] = [
            'errorText' => $error
        ];
    }
    echo json_encode($response);
}
?>
<? if ($initMap): ?>
    <div class="row">
        <div class="container">
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
            placemarks = <?=CUtil::PhpToJSObject($jsPlacemarks);?>;

        function init() {
            myMap = new ymaps.Map("map", {
                center: [56.412730, 37.260588],
                zoom: 4
            });

            for (var i in placemarks) {
                if (placemarks.hasOwnProperty(i)) {
                    var placemark = new ymaps.Placemark(placemarks[i].coordPoint, {
                        balloonContentHeader: '<strong>' + placemarks[i].name + '</strong>',
                        balloonContentBody: placemarks[i].phone + ', ' + placemarks[i].email,
                        balloonContentFooter: placemarks[i].city
                    },{
                        preset: 'islands#dotIcon',
                        iconColor: '#3b5998'
                    });
                    myMap.geoObjects.add(placemark);
                }
            }
        }
    </script>
<? endif; ?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>