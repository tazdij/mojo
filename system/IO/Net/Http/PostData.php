<?php

namespace Mojo\IO\Net\Http;


class PostData {

    public static function CreateFromInitialRequest() {
        // If there is a $_POST variable, we can use this instead of trying to
        // parse the input ourself

        $content_type = null;
        $files = null;
        $fields = null;
        $body = file_get_contents('php://input');

        if (!isset($_SERVER['HTTP_CONTENT_LENGTH']) || $_SERVER['HTTP_CONTENT_LENGTH'] == 0) {
            return false;
        }

        if (strpos($_SERVER['HTTP_CONTENT_TYPE'], 'multipart/form-data') !== false) {
            $content_type = 'multipart/form-data';
            // get the post data into the fields
            $fields = $_POST;

            // check for FILES
            if (isset($_FILES) && count($_FILES) > 0) {
                // Handle file input
                $files = $_FILES;
            }
        } elseif (strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false ||
            (strpos($_SERVER['HTTP_CONTENT_TYPE'], 'text/plain') !== false && isset($_POST) && count($_POST) > 0)) {
            $content_type = 'application/x-www-form-urlencoded';
            //$content_type
            $fields = $_POST;
        }

        $post = new PostData($content_type, $body, $fields, $files);
        return $post;
    }

    public function Create($content_type, $body, $files) {
        throw new \Exception('Function not implemented.');
    }

    protected $ContentType = null;
    protected $Body = null;
    protected $Fields = null;
    protected $Files = null;


    public function __construct($content_type, $body, $fields, $files) {
        $this->ContentType = $content_type;
        $this->Body = $body;
        $this->Fields = $fields;
        $this->Files = $files;

        if (strpos($this->ContentType, 'application/json') !== false) {
            // The body is JSON, Parse and place into fields
            $this->Fields = json_decode($this->Body, true);
        }
    }

    public function get($key, $default=null) {
        if (array_key_exists($key, $this->Fields)) {
            return $this->Fields[$key];
        } elseif (array_key_exists($key, $this->Files)) {
            return $this->Files[$key];
        }

        return $default;
    }

}
