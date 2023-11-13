<?php

return [
    "host" => env("ZIPKIN_HOST", "http://localhost"),
    "port" => env("ZIPKIN_PORT", "9411"),
    "allowed_methods" => ["GET", "PUT", "POST", "PATCH", "DELETE"],
];