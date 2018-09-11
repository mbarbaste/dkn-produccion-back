<?php
$auth = function ($request, $response, $next) {

    $token = $request->getHeaderLine('HTTP_TOKEN');

    $permitir = token_check($token);

    if($permitir) {
        // $response->getBody()->write($token);
        $response = $next($request, $response);
    } else {
        
        $response->getBody()->write('Token Check<hr>');
        $msg = array('Error' => '401','Msg' => 'Invalid Token');
        die(json_encode($msg));
    }       

    return $response;
};

function token($u, $i, $s) {

    $user = $u;
    $id = $i;
    $server = $s;

    $key = 'BobEsponjaSpa2017';

    $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
    ];

    $header = json_encode($header);
    $header = base64_encode($header);

    $payload = [
        'iss' => 'MartinBarbasteSoftware',
        'user' => $user,
        'id' => $id,
        'server' => $server
    ];

    $payload = json_encode($payload);
    $payload = base64_encode($payload);

    $signature = hash_hmac('sha256', "$header.$payload", $key, true);
    $signature = base64_encode($signature);

    $token = "$header.$payload.$signature";

    return $token;
}

function token_check($token_received) {

    $sites = array("jwt.app","slimapp.app");

    $token2array = explode(".", $token_received);
    $payload = $token2array[1];

    $data = base64_decode($payload);
    $data = json_decode($data, true);

    /*
    if (!in_array($data['server'], $sites)) {
        $token_received = [];
        return false;
    }
    */
    
    if($token_received === token($data['user'], $data['id'], $data['server'])) {
        return true;
    } else {
        return false;
    }
}