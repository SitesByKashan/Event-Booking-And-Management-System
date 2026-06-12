<?php

return [
    "host" => getenv("SMTP_HOST") ?: "smtp.gmail.com",
    "port" => (int) (getenv("SMTP_PORT") ?: 587),
    "username" => getenv("SMTP_USERNAME") ?: "mkashan2585@gmail.com",
    "password" => getenv("SMTP_PASSWORD") ?: "phzz jgtv tmvm qrhn",
    "from_email" => getenv("SMTP_FROM_EMAIL") ?: (getenv("SMTP_USERNAME") ?: ""),
    "from_name" => getenv("SMTP_FROM_NAME") ?: "EventHub"
];
