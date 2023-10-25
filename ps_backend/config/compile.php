<?php
/**
 * This config doesn't need frontend counterpart yet.
 * If you need to set per frontend, add require() script in the bottom (You can check settings.php config for pattern).
 * And make sure all frontend will have corresponding config file even if its blank only
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Additional Compiled Classes
    |--------------------------------------------------------------------------
    |
    | Here you may specify additional classes to include in the compiled file
    | generated by the `artisan optimize` command. These should be classes
    | that are included on basically every request into the application.
    |
    */

    'files' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled File Providers
    |--------------------------------------------------------------------------
    |
    | Here you may list service providers which define a "compiles" function
    | that returns additional files that should be compiled, providing an
    | easy way to get common files from any packages you are utilizing.
    |
    */

    'providers' => [
        //
    ],

];