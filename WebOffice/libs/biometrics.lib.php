<?php
namespace WebOffice;
class Biometrics{
    public function __construct() {
        
    }
    /**
     * Returns the face recognition script
     * @return string
     */
    public function face(): string{
        return "<script src=\"".ASSETS_URL.DS.'js'.DS."facial.js\" type=\"text/javascript\"></script>";
    }
    /**
     * Returns the passkey script
     * @return string
     */
    public function passkey(): string{
        return "<script src=\"".ASSETS_URL.DS.'js'.DS."passkey.js\" type=\"text/javascript\"></script>";
    }
}