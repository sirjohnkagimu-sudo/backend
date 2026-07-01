<?php

return [

    'disk' => env('FILESYSTEM_DISK', 'local'),

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
