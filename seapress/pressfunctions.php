<?php

function seafileLogin($username,$password,$hostname)
    {
        $fields = array(
            'username' => urlencode($username),
            'password' => urlencode($password),
            'hostname' => urlencode($hostname)
        );

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $hostname . '/api2/auth-token/');
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = json_decode(curl_exec($ch), true);

        curl_close($ch);
        return $result;
    }

function seafileApi($method = 'GET', $path = '', $data = array(), $token, $hostname)
    {
        $ch = curl_init();

        if (!preg_match('/^http(s|):/i', $path)) {
            $url = $hostname . $path;
        } else {
            $url = $path;
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, count($data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token ' . $token,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);

        if (curl_error($ch) || !isset($result)) {
            curl_error($ch);
        }

        curl_close($ch);

        return json_decode($result, true);
    }

function cut_last_occurence($string,$cut_off)
    {
        return strrev(substr(strstr(strrev($string), strrev($cut_off)),strlen($cut_off)));
    }

function formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array(' Bytes', ' KB', ' MB', ' GB', ' TB');
    $result=round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    $result=($result>0) ? $result : "0 Bytes"; #NAN issue
    return $result;
}

function time_elapsed_string($mtime){
    $output = array();
    $time_diff = time() - $mtime;

    // Seconds
    if( $time_diff < 60 ){
        $output[0] = "vor";
        $output[1] = $time_diff;
        $output[2] = "Sekunden";
    }
    elseif( $time_diff < 3600 ){
        $output[0] = "vor";
        $output[1] = floor($time_diff / 60);
        if($output[1] == 1) $output[2] = "Minute";
        else $output[2] = "Minuten";
    }
    elseif( $time_diff < 86400 ){
        $output[0] = "vor";
        $output[1] = floor($time_diff / 3600);
        if($output[1] == 1) $output[2] = "Stunde";
        else $output[2] = "Stunden";
    }
    elseif( $time_diff < 31536000 ){
        $output[0] = "vor";
        $output[1] = floor($time_diff / 86400);
        if($output[1] == 1) $output[2] = "Tag";
        else $output[2] = "Tagen";
    }
    elseif( $time_diff < 1576800000 ){
        $output[0] = "vor";
        $output[1] = floor($time_diff / 31536000);
        if($output[1] == 1) $output[2] = "Jahre";
        else $output[2] = "Jahren";
    }
    else {
        $output[0] = "Unknown";
    }
    return $output[0] ." ". $output[1] ." ". $output[2];
}

?>
