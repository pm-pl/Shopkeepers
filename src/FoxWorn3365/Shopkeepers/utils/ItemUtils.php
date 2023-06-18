<?php

namespace FoxWorn3365\Shopkeepers\utils;

use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\item\StringToItemParser;

class ItemUtils {
    public static final function encode(Item $item, bool $toObject = true) : array|object|null {
        trigger_error("Encoding item with ItemUtils::encode is deprecated! Use SerializedItem::encode to encode correctly!\nThrow in ItemUtils.php:10, item {$item->getName()}", E_USER_DEPRECATED);
        if ($toObject) {
            $ret = new \stdClass;
            $data = (new TypeConverter())->getItemTranslator()->toNetworkIdQuiet($item);
            $ret->id = $data[0];
            $ret->meta = $data[1];
            $ret->network = $data[2];
            return $ret;
        }
		return (new TypeConverter())->getItemTranslator()->toNetworkIdQuiet($item);
	}

    public static final function decode(int $id, int $meta, int $network) : ?Item {
        return (new TypeConverter())->getItemTranslator()->fromNetworkId($id, $meta, $network);
    }

    public static final function objectDecode(object $object) : ?Item {
        return (new TypeConverter())->getItemTranslator()->fromNetworkId($object->id, $object->meta, $object->network);
    }

    public static final function typeDecode(object $object) : ?Item {
        if (@$object->allowed != true) { trigger_error("Decoding item with ItemUtils::typeDecode is deprecated! Use SerializedItem::decode to decode correctly!\nThrow in ItemUtils.php:31 - WARNING: This error can be show also for a inside plugin error - PLEASE UPGRADE TO SerializedItem SCHEMA!", E_USER_DEPRECATED); }
        if (@$object->type === null) { $object->type = 1; }
        if ($object->type === 1) {
            return Utils::getIntItem($object->id, $object->meta);
        } else {
            return self::objectDecode($object);
        }
    }

    public static final function stringParser(string $string) : ?Item {
        return (new StringToItemParser())->parse($string);
    }
}