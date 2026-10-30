<?php
/* File do mốc: KHÔNG nạp Laravel. Dùng để tách bạch chi phí khởi động
   framework khỏi chi phí của chính PHP-FPM. Xóa sau khi đo xong. */
header('Content-Type: text/plain');
header('Cache-Control: no-store');
echo "pong ", number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2), " ms\n";
