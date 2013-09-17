<?php namespace Gloudemans\Gettext\Facades;

use Illuminate\Support\Facades\Facade;

class Gettext extends Facade {

/**
* Get the registered name of the component.
*
* @return string
*/
protected static function getFacadeAccessor() { return 'gettext'; }

}