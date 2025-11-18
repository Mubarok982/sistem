<?php
$token_fonnte = "Ag9s89dxD8Y4dATL3f8w"; 

function formatNomorWa($nomor) {
    $nomor = preg_replace('/[^0-9+]/', '', trim($nomor)); 

    if (substr($nomor, 0, 1) === '0') {
        return '62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 1) === '+') {
        return substr($nomor, 1);
    }

    return $nomor;
}
?>
