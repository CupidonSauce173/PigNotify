<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\lib;

use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm
{

    protected array $data = [];
    /** @var callable */
    private $callable;

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param Player $player
     * @see Player::sendForm()
     *
     */
    public function sendToPlayer(Player $player): void
    {
        $player->sendForm($this);
    }

    /**
     * @param Player $player
     * @param mixed $data
     */
    public function handleResponse(Player $player, $data): void
    {
        $this->processData($data);
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, $data);
        }
    }

    public function processData(&$data): void
    {

    }

    /**
     * @return callable|null
     */
    public function getCallable(): ?callable
    {
        return $this->callable;
    }

    public function jsonSerialize(): ?array
    {
        return $this->data;
    }
}
