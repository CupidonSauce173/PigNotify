<?php

declare(strict_types = 1);

namespace jojoe77777\FormAPI;

class FormAPI{

    /**
     * @param callable|null $function
     * @return SimpleForm
     */
    public function createSimpleForm(callable $function = null) : SimpleForm {
        return new SimpleForm($function);
    }
}
