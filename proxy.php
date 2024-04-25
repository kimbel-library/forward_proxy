<?php
// Create cookie
ini_set("session.cookie_secure", 1);
session_start();
$tmpfname = dirname(__FILE__).'/'.$_COOKIE['PHPSESSID'].'.txt';

// URL to fetch
$fetchUrl = $_GET["url"];

// Rewrite base URL should be of the form
// http(s)://domain/?url=?
$rewriteBaseUrl = sprintf("%s%s%s", empty($_SERVER["HTTPS"]) ? "http://" : "https://", $_SERVER["SERVER_NAME"], "/?url=");

$curl_options = array(
	CURLOPT_URL 			=> $fetchUrl,
	CURLOPT_HTTPGET 		=> TRUE,
	CURLOPT_RETURNTRANSFER	=> true,     // return web page
	CURLOPT_HEADER        	=> true,     // return headers in addition to content
	CURLOPT_FOLLOWLOCATION	=> true,     // follow redirects
	CURLOPT_ENCODING      	=> "",       // handle all encodings
	CURLOPT_AUTOREFERER   	=> true,     // set referer on redirect
	CURLOPT_CONNECTTIMEOUT	=> 120,
	CURLOPT_TIMEOUT       	=> 120,
	CURLOPT_MAXREDIRS     	=> 10,       // stop after 10 redirects
	CURLOPT_SSL_VERIFYPEER	=> true,     // Validate SSL Certificates
	CURLOPT_COOKIESESSION 	=> TRUE,
	CURLOPT_COOKIEJAR 		=> $tmpfname,
	CURLOPT_COOKIEFILE 		=> $tmpfname
);

// Todo: Add conditional to check for whitelisted domains
// Ex: if (!preg_match("/https?\:\/\/digitalcommons\.coastal\.edu\/?/", $fetchUrl)): exit;
$ch = curl_init();
curl_setopt_array($ch, $curl_options);
$response = curl_exec($ch);
if(!$response)
	var_dump(curl_error($ch);

$fetchUrlHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$fetchUrlHeaders = substr($response, 0, $fetchUrlHeaderSize);
$response = substr($response, $fetchUrlHeaderSize);
curl_close($ch);
$response = preg_replace("/<head.*>/i", "<head><base href='$fetchUrl' />", $response, 1); // Ensure images are loaded

// Present the proxied webpage
$doc = new DOMDocument(); 
$doc->loadHTML($response);

foreach($doc->getElementsByTagName('a') as $link) {
	$href = $link->getAttribute('href');
    //$href = preg_replace('/^\//', '', $link->getAttribute('href')); // Remove leading backslash from links
    $href = preg_match('/^https?:\/{2}/i', $href) ? $rewriteBaseUrl.$href : $rewriteBaseUrl.$fetchUrl.$href;
    $link->setAttribute('href', $href);
} 
$response = $doc->saveHTML();
$headers = headersToArray($fetchUrlHeaders);
header_remove();
header(sprintf("%s:%s", "Content-Type", $headers["Content-Type"]));
print $response;
exit;

function headersToArray($str) {
    $headers = array();
    $headersTmpArray = explode( "\r\n" , $str );
    for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
    {
        // Ignore the two \r\n lines at the end of the headers
        if ( strlen( $headersTmpArray[$i] ) > 0 )
        {
            // The headers start with HTTP status codes, which do not contain a colon. So, we filter them out.
            if ( strpos( $headersTmpArray[$i] , ":" ) )
            {
                $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}
?>
