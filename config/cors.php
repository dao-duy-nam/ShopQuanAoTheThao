<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Chỉ định đúng domain FE
    'allowed_origins' => ['http://localhost:3000'],

    'allowed_methods' => ['*'],

    'allowed_headers' => ['*'],

    // Phải bật credentials
    'supports_credentials' => true,

];