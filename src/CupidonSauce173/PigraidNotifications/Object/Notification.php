<?php


namespace CupidonSauce173\PigraidNotifications\Object;


use CupidonSauce173\PigraidNotifications\NotifLoader;

class Notification
{
    private string $player;
    private bool $displayed;
    private string $langKey;
    private array $varKeys;
    private string $event;
    private int $id;

    public function getId(): int{
        return $this->id;
    }

    public function setId(int $id): void{
        $this->id = $id;
    }

    public function getPlayer(): string{
        return $this->player;
    }

    public function setPlayer(string $player): void{
        $this->player = $player;
    }

    public function hasBeenDisplayed(): bool{
        return $this->displayed;
    }

    public function setDisplayed(bool $value): void{
        $this->displayed = $value;
    }

    public function getLangKey(): string{
        return $this->langKey;
    }

    public function setLangKey(string $key): void{
        $this->langKey = $key;
    }

    public function getVarKeys(): array{
        return $this->varKeys;
    }

    public function setVarKeys(array $keys): void{
        $this->varKeys = $keys;
    }

    public function getEvent(): string{
        return $this->event;
    }

    public function setEvent(string $event): void{
        $this->event = $event;
    }
}