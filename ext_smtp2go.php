<?php
register_callback('com_smtp2go', 'comconnect.deliver', 'send');

function com_smtp2go($evt, $stp, $data)
{
    // Fill this with the actual smtp2go server token credentials & sender email.
    $api_key = 'api-______________';
    $api_from_email = '____@______.com';
    $api_endpoint = 'https://api.smtp2go.com/v3/email/send';

    $payload = array();

    // smtp2go requires a verified domain-specific email.
    $payload['api_key'] = $api_key;
    $payload['sender'] = $api_from_email;
    $payload['to'] = $data['to'];
    $payload['subject'] = $data['subject'];
    $payload['text_body'] = $data['body']['plain'];
    if (!empty($data['body']['html'])) {
        $payload['html_body'] = $data['body']['html'];
    }

    $json = json_encode($payload);

    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json),
        'X-Smtp2go-Api-Key: ' .$api_key,
    );

    if (function_exists('curl_version')) {
        // Build the cURL request and send it off, to email the message.
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $api_endpoint);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $json);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

        $ret = curl_exec($c);

        // Check the returned document.
        $output = json_decode($ret);

        if (isset($output->data->error_code)) {
            // If smtp2go returns an error, fail.
            return 'comconnect.fail';
        } elseif ($output->data->succeeded) {
            // success = skip com_connect processing & hand over to stmp2go
            return 'comconnect.skip';
        } else {
            // unknown outcome
            return 'comconnect.fail';
        }
    } else {
        return 'comconnect.fail';
    }
}