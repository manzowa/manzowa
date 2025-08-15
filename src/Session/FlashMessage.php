<?php 
namespace App\Session;

class FlashMessage 
{
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    /**
     * AddMessage function
     *
     * @param [string] $type
     * @param [string] $message
     * 
     * @return void
     */
    public function addMessage(string $type, string $message) 
    {
        $_SESSION['flash_message'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    /**
     * AddError function
     *
     * @param [string] $key
     * @param [string] $message
     * @param string $type
     * 
     * @return void
     */
    public function addError(string $key, string $message, string $type="danger") 
    {
        $_SESSION['flash_error'][$key] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public function getMessages(): array
    {
        $messages = $_SESSION['flash_message']?? [];
        unset($_SESSION['flash_message']);
        return $messages;
    }
    public function getErrors(): array
    {
        $errors = $_SESSION['flash_error']?? [];
        unset($_SESSION['flash_error']);
        return $errors;
    }
}
