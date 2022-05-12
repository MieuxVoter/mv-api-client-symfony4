<?php


namespace App\Security;


/**
 * Some regular expressions for route and form validation.
 * This must be available somewhere else in the ecosystem already.   Oh well…
 *
 * Class Regexes
 * @package App\Security
 */
class Regexes
{

    // idea: match scientific notation as well
    const INT = "^-?[0-9]+$";
    // Unsigned integer
    const UINT = "^[0-9]+$";

    // Eg: 8d6e01a3-c139-4d28-8192-a210c65fa6be
    const UUID = "^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$";

}