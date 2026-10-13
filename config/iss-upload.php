<?php

return [
    'disk' => env('ISS_UPLOAD_DISK', 's3'),

    'acl' => env('ISS_UPLOAD_ACL'),

    'presigned_expiry' => env('ISS_UPLOAD_PRESIGNED_EXPIRY', '+10 minutes'),

    'path_prefix' => env('ISS_UPLOAD_PATH_PREFIX', 'uploads'),

    'validation' => [
        'mime_type' => env('ISS_IMAGE_VALID_MIME_TYPE', 'image/jpeg,image/gif,image/bmp,image/tiff,image/png,application/pdf'),
    ],

    'route' => [
        'prefix' => env('ISS_IMAGE_ROUTE_PREFIX', 'api'),

        'middleware' => env('ISS_IMAGE_ROUTE_MIDDLEWARE', 'hub.auth'),
    ],
];
