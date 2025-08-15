<?php

/**
 * Bootstrap
 * 
 * User: Christian SHUNGU <christianshungu@gmail.com>
 * Date: 11.08.2024
 * php version 8.2
 *
 * @category App
 * @package  App
 * @author   User: Christian SHUNGU <christianshungu@gmail.com>
 * @license  See LICENSE file
 * @link     https://manzowa.com
 */

namespace App 
{
    if (!\function_exists('App\path')) {
        /**
         * Function path
         *
         * @param ?string $path
         * @return string
         */
        function path(): ?string
        {
            $arguments = func_get_args();
            return join(DIRECTORY_SEPARATOR, [APP_ROOT, ...$arguments]);
        }
    }
    if (!\function_exists('App\integrity')) {
        /**
         * Function integrity
         *
         * @param ?string $path
         * @return string
         */
        function integrity(): ?string
        {
            $key = getenv('APP_KEY') ?? "";
            $hash = hash_hmac('sha256', $key, 'cinq petis chats');
            return $hash;
        }
    }
    if (!\function_exists('App\redirectToRoute')) {
        /**
         * Function redirectToRoute
         *
         * @param ?string $routeName
         * 
         * @return string
         */
        function redirectToRoute(?string $routeName = null,  $permanent = false)
        {
            $route = !is_null($routeName) ? $routeName : "";
            $scheme = $_SERVER['REQUEST_SCHEME'] ?? "";
            $host  = $_SERVER['HTTP_HOST'] ?? "";
            $strUrl = $scheme . "://" . $host . $route;
            header('Location: ' . $strUrl, true, $permanent ? 301 : 302);
            exit();
        }
    }
    if (!\function_exists('App\user')) {
        /**
         * Function user
         *
         * 
         * @return string
         */
        function user(): ?\App\Model\User
        {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : false;
            if (!$user) {
                return null;
            }
            return $user;
        }
    }
    if (!\function_exists('App\session')) {
        /**
         * Function session
         * 
         * @return void
         */
        function session(): void
        {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }
    }
    if (!function_exists("App\logger")) {
        /**
         * Function logger
         * 
         * @param string $message       - Le message d'erreur qui doit être stocké
         * @param int    $type          - Spécifie la destination du message d'erreur
         * @param string $destination   - 
         * @param string $extra_headers - 
         * 
         * @return void
         */
        function logger(
            string $message,
            int $type = 0,
            $destination = "",
            $extra_headers = ""
        ): void {
            error_log($message, $type, $destination, $extra_headers);
        }
    }
    if (!function_exists("App\loggerException")) {
        /**
         * Function loggerException
         * 
         * @param Exception $e - Le message d'erreur qui doit être stocké
         * 
         * @return void
         */
        function loggerException(\Exception $e): void {
            $msg= sprintf("%s sur la ligne ( %s ) : %s", 
                $e->getFile(), $e->getLine(), $e->getMessage()
            );
            logger($msg);
        }
    }
    if (!function_exists("App\generateToken")) {
        /**
         * Function generateToken
         * 
         * @return  string
         */
        function generateToken(): string {
            $token = base64_encode(
                bin2hex(openssl_random_pseudo_bytes(24)) . time()
            );
            return $token;
        }
    }
    if (!function_exists("App\beareToken")) {
        /**
         * Function generateToken
         * 
         * @return  string
         */
        function beareToken(string $authHeader): ?string 
        {
            $token = null;
            if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches) === 1) {
                $token = $matches[1];
            }
            return $token;  
        }
    }
}
