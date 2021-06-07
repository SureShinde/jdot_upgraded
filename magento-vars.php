<?php
// enable, adjust and copy this code for each store you run
// Store #0, default one
//if (isHttpHost("example.com")) {
//    $_SERVER["MAGE_RUN_CODE"] = "default";
//    $_SERVER["MAGE_RUN_TYPE"] = "store";
//}

$uri = '';
if(array_key_exists('REQUEST_URI',$_SERVER)){
    $uri = $_SERVER['REQUEST_URI'];
}

switch($uri){
    case "/us":
        header("Location: https://us.junaidjamshed.com/");
        exit();

    case "/ca":
        header("Location: https://us.junaidjamshed.com/ca");
        exit();

    case "/au":
        header("Location: https://au.junaidjamshed.com/");
        exit();

    case "/nz":
        header("Location: https://au.junaidjamshed.com/nz");
        exit();

    case "/uk":
        header("Location: https://uk.junaidjamshed.com/");
        exit();

    case "/za":
        header("Location: https://za.junaidjamshed.com/");
        exit();
}
if(!isset($_COOKIE['countrycurrency']) && strpos($uri,"pagespeed") === FALSE)
{
    if (isset($_POST["landing-currency"]) && !empty(($_POST["landing-currency"]))) {
        $value = $_POST["landing-currency"];
        if($value == "AUD"){
            header("Location: https://au.junaidjamshed.com/");
            exit();
        }
        if($value == "NZD"){
            header("Location: https://au.junaidjamshed.com/nz/");
            exit();
        }
        if($value == "GBP"){
            header("Location: https://uk.junaidjamshed.com/");
            exit();
        }
        if($value == "US"){
            header("Location: https://us.junaidjamshed.com/");
            exit();
        }
        if($value == "CA"){
            header("Location: https://us.junaidjamshed.com/ca/");
            exit();
        }
        if($value == "ZA"){
            header("Location: https://za.junaidjamshed.com/");
            exit();
        }

        else{
            setcookie("countrycurrency", $value);

            header('Location: /directory/currency/switch/currency/'.$value);
            exit();
        }
    }
    if($uri == "/" || $uri == "/index.php")
    {
        header('Location:' . 'select-country');
        exit();
    }
}
else
{
    if(strpos($uri,"/select-country") !== FALSE)
    {
        header('Location:' . '/');
        exit();
    }
}

function isHttpHost($host)
{
    if (!isset($_SERVER['HTTP_HOST'])) {
        return false;
    }
    return strpos(str_replace('---', '.', $_SERVER['HTTP_HOST']), $host) === 0;
}
