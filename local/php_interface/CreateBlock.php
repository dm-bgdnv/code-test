<?php

namespace CustomArea;

use \Bitrix\Main\Loader,
    \Bitrix\Iblock\IblockTable;

Loader::includeModule("iblock");

class CreateIBlock
{

    const ERROR_IBLOCK_TYPE_AND_CODE = "Указанный тип информационного блока или информационный блок с указанным типом уже существуют";
    const ERROR_IBLOCK_ADD_TYPE_AND_CODE = "Произошла ошибка при добавлении информационного блока";
    const SUCCESS_IBLOCK_ADD_SUCCESS = "Информационный блок успешно добавлен";
    const IBLOCK_TYPE_SECTIONS = "Разделы";
    const IBLOCK_TYPE_ELEMENTS = "Элементы";

    public ?array $errors = null;

    private string $name;
    private string $code;
    private string $type;
    private ?array $properties;
    private ?array $elements;

    /**
     * @param $name : Название информационного блока
     * @param $code : Символьный код информационного блока
     * @param $type : Тип информационного блока
     * @param $properties : Массив свойств для информационного блока
     * @param $elements : Массив элементов для информационного блока
     */

    public function __construct(string $name, string $code, string $type, ?array $properties, ?array $elements)
    {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->properties = $properties;
        $this->elements = $elements;
    }

    /**
     * Инициализация добавления нового информационного блока
     * @return bool
     */

    public function initIblock(): bool
    {
        if (!$this->isNotTypeIblock())
        {
            $this->setErrors(self::ERROR_IBLOCK_TYPE_AND_CODE);
            return false;
        }

        if (!$this->addTypeIblock())
        {
            $this->setErrors(self::ERROR_IBLOCK_ADD_TYPE_AND_CODE);
            return false;
        }

        return true;
    }

    /**
     * Добавление информационного блока с указанными названием, символьным кодом и типом
     * @return int
     */

    private function addTypeIblock(): bool
    {
        if (!$this->addType()) return false;

        $iblockId = $this->addIblock();
        $this->addIblockProperties($iblockId);
        $this->addIblockElements($iblockId);

        return true;
    }

    /**
     * Добавление типа информационного блока
     * @return int
     */

    private function addType(): bool
    {
        $type = new \CIBlockType;

        $typeFields = [
            "ID" => $this->type,
            "SECTIONS" => "Y",
            "IN_RSS" => "N",
            "SORT" => 100,
            "LANG" => [
                "ru" => [
                    "NAME" => $this->name,
                    "SECTION_NAME" => self::IBLOCK_TYPE_SECTIONS,
                    "ELEMENT_NAME" => self::IBLOCK_TYPE_ELEMENTS
                ]
            ]
        ];

        return $type->Add($typeFields);
    }

    /**
     * Добавление информационного блока
     * @return int
     */

    private function addIblock(): int
    {
        $iblock = new \CIBlock;

        $iblockFields = [
            "ACTIVE" => "Y",
            "NAME" => $this->name,
            "CODE" => $this->code,
            "IBLOCK_TYPE_ID" => $this->type,
            "SITE_ID" => [$this->getIdSite()],
            "SORT" => 100
        ];

        return $iblock->Add($iblockFields);
    }

    /**
     * Проверка на существование указанных информационного блока и типа
     * @return bool
     */

    private function isNotTypeIblock(): bool
    {
        $iblockType = IblockTable::getList([
            "select" => ["ID"],
            "filter" => ["=IBLOCK_TYPE_ID" => $this->type, "=CODE" => $this->code]
        ])->fetch();
        
        if ($iblockType["ID"])
        {
            return false;
        }

        return true;
    }

    /**
     * Добавление свойств для информационного блока
     * @param $id : Идентификатор информационного блока
     * @return bool
     */
    private function addIblockProperties(int $id): bool
    {
        if (empty($this->properties) && empty($id)) return false;

        $tempProperties = [];

        foreach ($this->properties as $property) {
            $iblockProperty = new \CIBlockProperty;

            $fieldsProperty = [
                "IBLOCK_ID" => $id,
                "ACTIVE" => "Y",
                "NAME" => $property["name"],
                "SORT" => $property["sort"],
                "CODE" => $property["code"],
                "PROPERTY_TYPE" => $property["type"],
            ];

            if ($property["code"] === "COORDINATES") $fieldsProperty["USER_TYPE"] = "map_yandex";

            if ($idProperty = $iblockProperty->Add($fieldsProperty) > 0)
            {
                $tempProperties[] = $idProperty;
            }
        }

        if (count($tempProperties) !== count($this->properties)) return false;

        return true; 
    }

    /**
     * Добавление элементов для информационного блока
     * @param $id : Идентификатор информационного блока
     * @return bool
     */
    private function addIblockElements(int $id): bool
    {
        if (empty($this->elements) && empty($id)) return false;

        $tempElements = [];
        $propertiesElement = [];

        foreach ($this->elements as $element) {
            $iblockElement = new \CIBlockElement;

            $propertiesElement = [
                "PHONE" => $element["phone"],
                "EMAIL" => $element["email"],
                "COORDINATES" => $element["coordinates"],
                "CITY" => $element["city"]
            ];
    
            $fieldsElement = [
                "IBLOCK_ID" => $id,
                "PROPERTY_VALUES" => $propertiesElement,
                "NAME" => $element["name"],
                "ACTIVE" => "Y"
            ];

            if ($idElement = $iblockElement->Add($fieldsElement) > 0)
            {
                $tempElements[] = $idElement;
            }
        }

        if (count($tempElements) !== count($this->elements)) return false;

        return true; 
    }
   
    /**
     * Запись ошибки в результат выполнения функции
     * @param $error : Текст возвращаемой ошибки
     * @return string
     */
    private function setErrors(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Получение идентификатора текущего сайта
     * @return string
     */
    private function getIdSite(): string
    {
        $dbSite = \CSite::GetList($by = "sort", $order = "desc", ["DOMAIN" => $_SERVER["SERVER_NAME"]]);
        if ($fieldsSite = $dbSite->Fetch())
        {
            return $fieldsSite["LID"];
        }
    }

}
