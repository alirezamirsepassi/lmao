<?php
/**
 * LMAO.php
 *
 * Author: topster21
 * Github: @see github.com/topster21/lmao
 * Date: 2/18/17
 * Time: 5:00 PM
 *
 *
 *
 */
namespace Topster21\LMAO\Facades;

use Illuminate\Support\Facades\Facade;

class LMAO extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'topster21-lmaoclient';
    }
}