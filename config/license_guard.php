<?php

return [

    // License key riêng cho site này (set trong .env)
    'key' => env('LICENSE_GUARD_KEY', ''),

    // Mã sản phẩm để map với product_code bên server
    'product_code' => env('LICENSE_GUARD_PRODUCT_CODE', ''),

    // TTL cache token từ server (giây)
    'cache_ttl' => env('LICENSE_GUARD_CACHE_TTL', 300),

    // Cho qua tạm nếu lỗi kết nối server?
    'grace_on_error' => env('LICENSE_GUARD_GRACE_ON_ERROR', false),

    // Tắt toàn bộ guard (ví dụ cho local dev)
    'disabled' => env('LICENSE_GUARD_DISABLED', false),
];
