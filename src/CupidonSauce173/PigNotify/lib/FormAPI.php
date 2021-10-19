<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\lib;

class FormAPI
{

    /**
     * @param callable|null $function
     * @return SimpleForm
     */
    public function createSimpleForm(callable $function = null): SimpleForm
    {
        return new SimpleForm($function);
    }
}