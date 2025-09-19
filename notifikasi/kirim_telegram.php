<?php
/**
 * Fungsi untuk mengirim pesan ke Telegram
 * @param string $chat_id - ID chat Telegram penerima
 * @param string $pesan - Pesan yang akan dikirim
 * @return mixed - Response dari Telegram API
 */
function kirim_telegram($chat_id, $pesan) {
    // Ganti dengan token bot Telegram kamu
    $bot_token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : 'MASUKKAN_TOKEN_BOT_TELEGRAM_KAMU_DI_CONFIG.PHP';
    
    // URL API Telegram
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    
    // Data yang akan dikirim
    $data = [
        'chat_id' => $chat_id,
        'text' => $pesan,
        'parse_mode' => 'HTML'
    ];
    
    // Opsi HTTP
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    // Kirim request
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}

/**
 * Fungsi alternatif jika file_get_contents tidak diizinkan
 * Gunakan cURL jika tersedia
 */
function kirim_telegram_curl($chat_id, $pesan) {
    $bot_token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : 'MASUKKAN_TOKEN_BOT_TELEGRAM_KAMU_DI_CONFIG.PHP';
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $pesan,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

/**
 * Fungsi wrapper - coba file_get_contents dulu, jika gagal coba cURL
 */
function kirim_notifikasi_telegram($chat_id, $pesan) {
    // Coba dengan file_get_contents
    $result = kirim_telegram($chat_id, $pesan);
    
    // Jika gagal, coba dengan cURL
    if ($result === false && function_exists('curl_init')) {
        $result = kirim_telegram_curl($chat_id, $pesan);
    }
    
    return $result;
}

// Contoh penggunaan (uncomment untuk testing):
/*
if (isset($_GET['test']) && $_GET['test'] == '1') {
    $test_result = kirim_notifikasi_telegram('MASUKKAN_CHAT_ID_KAMU', '✅ Test notifikasi Telegram berhasil!');
    echo "<pre>";
    print_r($test_result);
    echo "</pre>";
}
*/
?>
