<?php
declare(strict_types=1);

namespace tornaido;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class GodMode extends PluginBase implements Listener
{

    private Config $config;

    private array $gods = [];

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $configPath = $this->getDataFolder() . "config.yml";

        if(!file_exists($configPath))
        {
            $this->getLogger()->notice("I've spotted it's your first time using GodMode! To change GodMode's configuration go to pluginData -> GodMode -> config.yml & adjust everything according to your own liking.");
        }

        $this->config = $this->getConfig();
        $this->saveDefaultConfig();

        $configVersion = $this->config->get("Version", 1.0);
        $pluginVersion = $this->getDescription()->getVersion();

        if(version_compare($pluginVersion, $configVersion, "gt"))
        {
            $this->getLogger()->warning("§cI've spotted you were using an outdated version of the plugin config, It is advised to delete your GodMode plugin data & redo all your configurations for optimal performance");
        }



        $this->getLogger()->info("§8§l(§2+§8) §r§aPlugin enabled!");
    }

    public function onDisable(): void
    {
        $this->getLogger()->info("§8§l(§4-§8) §r§cPlugin disabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(strtolower($command->getName()) === "godmode")
        {
            $prefix = $this->config->get("Prefix", "§8[§l§6G§eO§6D§r§8]§r ");

            if($sender instanceof Player)
            {
                if(!isset($this->gods[$sender->getName()]))
                {
                    $this->gods[$sender->getName()] = $sender->getName();
                    $sender->sendMessage($prefix . $this->config->get("EnableMessage", "§aYou have been granted the power to the infinity stone!"));

                    if($this->config->get("Strength", true))
                    {
                        $sender->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 999999999, $this->config->get("strengthAmplifier", 5), false, false));
                    }

                    if($this->config->get("Invisibility", true))
                    {
                        $sender->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 999999999, 5, false, false));
                    }
                }
                else
                {
                    unset($this->gods[$sender->getName()]);
                    $sender->sendMessage($prefix . $this->config->get("DisableMessage", "§cYour power has been taken away from you!"));

                    if($this->config->get("Strength", true))
                    {
                        $sender->getEffects()->remove(VanillaEffects::STRENGTH());
                    }

                    if($this->config->get("Invisibility", true))
                    {
                        $sender->getEffects()->remove(VanillaEffects::INVISIBILITY());
                    }
                }
            }
            else
            {
                $sender->sendMessage($prefix . "§cThis command is only available for use to players");
                return true;
            }
            return true;
        }
        return false;
    }

    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Player)
        {
            if (isset($this->gods[$entity->getName()]))
            {
                $event->cancel();
            }

        }
    }

}