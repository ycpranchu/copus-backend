<?php
$protocolFolder = "uploads/protocols/123";

if (!file_exists($protocolFolder)) {
    if (!mkdir($protocolFolder, 0777, true)) {
        die(json_encode(['message' => 'Failed to create directory: ' . $protocolFolder]));
    }
}
