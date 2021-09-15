<?php

declare(strict_types = 1);


namespace CupidonSauce173\PigNotify\Object;

use Volatile;

class Notification extends Volatile
{
    private string $player;
    private bool $displayed;
    private string $langKey;
    private array $varKeys;
    private string $event;
    private int $id;

    /**
     * @return int
     */
    function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    function getPlayer(): string
    {
        return $this->player;
    }

    /**
     * @param string $player
     */
    function setPlayer(string $player): void
    {
        $this->player = $player;
    }

    /**
     * @return bool
     */
    function hasBeenDisplayed(): bool
    {
        return $this->displayed;
    }

    /**
     * @param bool $value
     */
    function setDisplayed(bool $value): void
    {
        $this->displayed = $value;
    }

    /**
     * @return string
     */
    function getLangKey(): string
    {
        return $this->langKey;
    }

    /**
     * @param string $key
     */
    function setLangKey(string $key): void
    {
        $this->langKey = $key;
    }

    /**
     * @return array
     */
    function getVarKeys(): array
    {
        return $this->varKeys;
    }

    /**
     * @param array $keys
     */
    function setVarKeys(array $keys): void
    {
        $this->varKeys = $keys;
    }

    /**
     * @return string
     */
    function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    function setEvent(string $event): void
    {
        $this->event = $event;
    }
}