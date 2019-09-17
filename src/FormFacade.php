<?php

namespace Vespera\LaravelForm;

use Illuminate\Support\Facades\Facade;

class FormFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'form';
    }
}
