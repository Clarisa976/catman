<?php

return [
    'providers' => array_values(array_filter(array_map('trim', explode(',', env('BOOK_METADATA_PROVIDERS', 'open_library,manual'))))),

    'google_books_api_key' => env('GOOGLE_BOOKS_API_KEY'),
];
