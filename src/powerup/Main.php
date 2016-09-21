<?php

namespace powerup;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\scheduler\PluginTask;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class PTask extends PluginTask {
  
  public function onRun($tick) {
    
    foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
      if(array_key_exists($player->getName(), $this->getOwner()->spell)) {
      $cspell = $this->getOwner()->spell[$player->getName()];
      $player->sendTip(C::BLUE ."Magic point: ". C::GOLD . $cspell ."/". $this->getOwner()->cfg->get("spells"));
        }
      }
    }
 }

class Main extends PluginBase implements Listener {
  
  public $cfg;
  public $spell = [];
  
  public function onEnable() {
    
    if(!is_dir($this->getDataFolder())) {
      mkdir($this->getDataFolder());
      }
      
    $this->cfg = new Config($this->getDataFolder() ."config.yml", Config::YAML, [
    "spells" => 50,
    
    "Spell" => [
    "heal" => [
    "spell" => 5,
    "hearts" => 5
    ],
    
    "leap" => [
    "id" => 8,
    "spell" => 15,
    "level" => 1,
    "duration" => 5
    ],
    
    "reneration" => [
    "id" => 10,
    "spell" => 20,
    "level" => 1,
    "duration" => 5
    ],
    
    "strength" => [
    "id" => 5,
    "spell" => 10,
    "level" => 1,
    "duration" => 10
    ]
    ]
    
    ]);
    
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new PTask($this), 20);
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    
    }
    
    public function onCommand(CommandSender $s, Command $cmd, $label, array $args) {
      
      switch($cmd->getName()) {
        
        case "pu":
        if(isset($args[0])) {
          
          if($args[0] == "heal") {
            
            if($s->hasPermission("pu.heal")) {
            $heal = $this->cfg->get("Spell");
            
            if($this->spell[$s->getName()] > $heal["heal"]["spell"]) {
              $this->spell[$s->getName()] = $this->spell[$s->getName()] - $heal["heal"]["spell"];
              
              $s->setHeart($s->getHeart + $heal["heal"]["hearts"]);
              } else {
                $s->sendMessage(C::RED ."not enough spell");
                }
              }
             }
             
             $value = $this->cfg->get("Spell");
          if(isset($value[$args[0]])) {
            
            if(!$s->hasPermission("pu.". $args[0])) {
              $s->sendMessage(C::RED ."You dont have permission to use this spell");
              } else {
              
            $data = $value[$args[0]];
            
            if($this->spell[$s->getName()] > $data["spell"]) {
            
            $this->spell[$s->getName()] = $this->spell[$s->getName()] - $data["spell"];
            
            $effect = Effect::getEffect($data["id"]);
            $effect->setDuration($data["duration"] * 20);
            $effect->setVisible(true);
            $effect->setAmplifier($data["level"]);
            
            $s->addEffect($effect);
            $s->sendMessage(C::GREEN ."Succeed used §8[". C::YELLOW . $args[0] ."§8] ". C::GREEN ."power up for ". $data["duration"] ." secconds!");
            } else {
              $s->sendMessage(C::RED ."Not enough spell!");
              }
              
           } 
           
         } else {
           $s->sendMessage(C::RED ."Power up not found");
           }
           
         } else {
           $s->sendMessage("Usage: /pu <powerup>");
           }
        break;
        
       case "pulist":
         
         $s->sendMessage(C::BLUE ."------". C::YELLOW ."Avaliable ". C::GOLD ."Spell:". C::BLUE ."------");
         foreach($this->cfg->get("Spell") as $key => $value) {
           $s->sendMessage(C::BLUE ."- ". C::GREEN . $key);
           }
         break;
         }
     }
     
     public function onJoin(PlayerJoinEvent $ev) {
       
       if(!array_key_exists($ev->getPlayer()->getName(), $this->spell)) {
         $this->spell[$ev->getPlayer()->getName()] = $this->cfg->get("spells");
         }
       }
       
    public function onDeath(PlayerDeathEvent $ev) {
      $cause = $ev->getEntity()->getLastDamageCause();
      
      if($cause instanceof EntityDamageEvent) {
        if($cause instanceof EntityDamageByEntityEvent) {
          $dmgr = $cause->getDamager();
          $v = $ev->getEntity();
          
          if($dmgr instanceof Player && $v instanceof Playee) {
            $this->spell[$dmgr->getName()]++;
            }
          }
        }
      }
    }
