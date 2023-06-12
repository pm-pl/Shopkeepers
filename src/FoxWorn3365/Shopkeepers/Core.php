<?php
declare(strict_types=1);

namespace FoxWorn3365\Shopkeepers;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerEntityInteractEvent as Interaction;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\InvMenu;
use pocketmine\item\Item;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use \pocketmine\level\Position;
use \pocketmine\entity\Villager;

use FoxWorn3365\Shopkeepers\Menu\CreateMenu;
use FoxWorn3365\Shopkeepers\Menu\EditMenu;

class Core extends PluginBase implements Listener {
    protected object $menu;

    public function onLoad() : void {
        $this->menu = new \stdClass;
    }

    public function onEnable() : void {
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}

        // Create the config folder if it does not exists
        @mkdir($this->getDataFolder());

        // Load config if it does not exists
        if (file_exists($this->getDataFolder() . "config.yml")) {
            $this->menu = json_decode(file_get_contents($this->getDataFolder() . "config.yml"))->menus;
        }

        // Register event listener
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerEntityInteract(Interaction $event) : void {
        $menu = new CreateMenu($this->getDataFolder());
        $menu->create()->send($event->getPlayer());
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool{
        if (!($sender instanceof Player)) {
            $sender->sendMessage("This command can be only executed by in-game players!");
            return false;
        }
        $shop = new ConfigManager($sender, $this->getDataFolder());
        if ($args[0] == "list") {
            if ($shop->is()) {
                $list = "";
                foreach ($shop->get() as $title => $item) {
                    $list .= "\nMenu: {$title}";
                }
                $sender->sendMessage("Your shops: {$list}");
                return true;
            } else {
                $sender->sendMessage("You don't have any shop(s) here!");
            }
        } elseif ($args[0] == "create") {
            if (empty($name = $args[1])) {
                $name = $this->generateRandomString(7);
            }
            // Create the config 
            // OOOO why are u running? before, check if there's also an existing name
            if (@$shop->get()?->{$name} !== null) {
                $sender->sendMessage("You already have a shop called {$name}!");
                return false;
            }
            $newshop = new \stdClass;
            $newshop->title = $name;
            $newshop->owner = $sender->getName();
            $newshop->admin = false;
            $newshop->items = [];
            $shop->set($name, $newshop);
            $menu = new EditMenu($shop, $name);
            $menu->create()->send($sender);
            return true;
        } elseif ($args[0] == "edit") {
            $name = $args[1];
            if (@$shop->get()?->{$name} === null) {
                $sender->sendMessage("You don't have a shop called {$name}!");
                return false;
            }
            // Let's open the edit interface
            $menu = new EditMenu($shop, $name);
            $menu->create()->send($sender);
            return true;
        } elseif ($args[0] == "info") {
            $sender->sendMessage("Shopkeepers for PMMP by FoxWorn3365\nGitHub: https://github.com/FoxWorn3365/Shopkeepers");  
            return true;
        } elseif ($args[0] == "summon") {
            $name = $args[1];
            if (@$shop->get()?->{$name} === null) {
                $sender->sendMessage("You don't have a shop called {$name}!");
                return false;
            }
            // Let's summon a villager with these data
            $pos = $sender->getLocation();
            $villager = new Villager($pos);
            $villager->spawnToAll();
            return true;
        }
        var_dump($args);
        return true;
    }

    // https://stackoverflow.com/questions/4356289/php-random-string-generator
    // I'm only lazy
    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}