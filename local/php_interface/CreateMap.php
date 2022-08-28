<?php

namespace CustomArea;

use \Bitrix\Main\Loader,
    \Bitrix\Iblock\IblockTable,
    \Bitrix\Iblock\PropertyTable,
    \Bitrix\Iblock\ElementTable;

Loader::includeModule("iblock");

class CreateMap
{
    const ERROR_IBLOCK_FIND = "Указанный информационный блок не найден";

    public ?array $errors = null;

    private string $id;
    private ?array $properties;
    private ?array $elements;
    public ?array $mapElements;

    /**
     * @param $id : Идентификатор информационного блока
     */

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Инициализация сбора 
     * @return bool
     */
    public function initIblock(): bool
    {
        if (is_null($iblockId = $this->findIblock()))
        {
            $this->setErrors(self::ERROR_IBLOCK_FIND);
            return false;
        }

        $this->getIblockProperties($iblockId);
        $this->getIblockElements($iblockId);

        return true;
    }

    /**
     * Поис кинформационного блока по идентификатору
     * @return ?int
     */
    private function findIblock()
    {
        $iblock = IblockTable::getList([
            "filter" => ["ID" => $this->id]
        ])->fetch();

        return $iblock["ID"];
    }

    /**
     * Получение списка свойств в указанном информационном блокеarray
     * @param $id : Идентификатор информационного блока
     * @return array
     */
    private function getIblockProperties(int $id): array
    {
        $properties = PropertyTable::getList(array(
            "select" => array("*"),
            "filter" => array("IBLOCK_ID" => $id)
        ))->fetchAll();

        foreach ($properties as $property) {
            $this->properties[] = $property["CODE"];
        }

        return $this->properties;
    }

    /**
     * Получение списка элементов в указанном информационном блоке
     * @param $id : Идентификатор информационного блока
     * @return array
     */
    private function getIblockElements(int $id): array
    {
        $elements = ElementTable::getList([
            "select" => ["ID", "NAME", "IBLOCK_ID"],
            "filter" => ["IBLOCK_ID" => $id],
            "cache" => ["ttl" => 3600]
        ]);

        while ($element = $elements->fetch()) {
            foreach ($this->properties as $property) {
                $itemProperty = \CIBlockElement::GetProperty(
                    $element["IBLOCK_ID"],
                    $element["ID"],
                    [],
                    ["CODE" => $property]
                );
                if ($itemProp = $itemProperty->Fetch()) {
                    $element["PROPERTIES"][$property] = $itemProp;
                }
            }
            $this->elements[] = $element;
        }

        return $this->elements;
    }

    /**
     * Вывод сфомированного списка элементов через public св-во
     * @return array
     */
    public function getRenderElements(): array
    {
        $this->mapElements = $this->elements ?? [];
        return $this->mapElements;
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

}
