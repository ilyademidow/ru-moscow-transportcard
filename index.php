<?php
/**
 * Created by PhpStorm.
 * User: Ilya Demidov
 * Date: 18.03.2016
 **/
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 *
 * @param $url string
 **/
function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "UTF-8",       // handle all encodings
        CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );
    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}
$YOUR_EMAIL = '';

$FILENAME = 'previous_bal.txt';
$CARDNUM = '03317937536';
$CARDTYPEID = '3ae427a1-0f17-4524-acb1-a3f50090a8f3'; // Permanent ID for all cards

$result = get_web_page("https://strelkacard.ru/api/cards/status/?cardnum={$CARDNUM}&cardtypeid={$CARDTYPEID}");
if(!file_exists($FILENAME)) {
    file_put_contents($FILENAME,'');
}
$oldBalance = file_get_contents($FILENAME);
$card = json_decode($result['content']);
$newBalance = $card->balance;
$formatedNewBalance = ($newBalance/100)." руб.";
$formatedTicketPrice = (abs($oldBalance-$newBalance)/100)." руб.";
if($oldBalance != $newBalance) {
    if ($oldBalance > $newBalance) {
        $mailText = 'Списано ' . $formatedTicketPrice . ', баланс на карте ' . $formatedNewBalance;
        $mailSubj = 'Списание';
    } else {
        $mailText = 'Пополнено ' . $formatedTicketPrice . ', баланс на карте ' . $formatedNewBalance;
        $mailSubj = 'Поступление';
    }
    mail($YOUR_EMAIL, $mailSubj, $mailText);
    file_put_contents($FILENAME, $newBalance);
}
