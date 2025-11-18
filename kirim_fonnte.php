<?php
include_once "config_fonnte.php";

function kirimWaFonnte($tujuan, $pesan) {
    global $token_fonnte;

    $tujuan = formatNomorWa($tujuan);
    sleep(1); 
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => [
            'target' => $tujuan,
            'message' => $pesan,
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: $token_fonnte"
        ],
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("Fonnte error: $err");
    }

    return $response;
}
