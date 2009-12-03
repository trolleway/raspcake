<?
/**
 * Простая авторизация с http://com.spweb.ru/archives/26
 */
    class ValidationComponent extends Object {

    function doAuth() {

    $users = array('redaktor'=>'shachlo');

    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && isset($users[$_SERVER['PHP_AUTH_USER']]) && ($users[$_SERVER['PHP_AUTH_USER']] == $_SERVER['PHP_AUTH_PW'])) $allowed = 1;

    if (!isset($allowed) || ($allowed <> 1)) {
    header('WWW-Authenticate: Basic realm="Auth page. Sheet site. Local"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Не положено.';
    exit;
    }

    }

    }
?>