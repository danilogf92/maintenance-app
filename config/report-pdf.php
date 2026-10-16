<?php

return [
    'ttl_minutes' => (int) env('REPORT_PDF_TTL_MINUTES', 60),
    'timeout' => 180,
    'python' => env('REPORT_PDF_PYTHON', '/usr/bin/python3'),
];
