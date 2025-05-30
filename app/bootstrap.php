<?php

/**
 * Bootstrap
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App
 * @package  App
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App
{
    if (!\function_exists('path')) {
        /**
         * Function path
         *
         * @param ?string $path
         * @return string
         */
        function path(): ?string
        {
            $arguments = func_get_args();
            return join(DS, [APP_ROOT, ...$arguments]);
        }
    }
}