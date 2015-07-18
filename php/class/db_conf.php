<?php namespace scripts\db;

/**
 * Author: Lennard Moll
 * Licence: GNU GPL 3
 * Last edited 18 July 2015
 */


class db_config extends \PDO
{
    protected $pdo;
    protected $options;
    
    public function __construct($info = array(), $options = array()) {
        // Validate the info if it isn't empty
        if (count($info) == 0) {
            throw new \Exception("DB information left empty", '500');
        }
        
        if (!is_array($info)) {
            throw new \Exception("pdo settings isn't an array", '500');
        }
        
        // get the elements if missing throw exception
        if (!array_key_exists("dbname", $info) || !array_key_exists("username", $info) || !array_key_exists("password", $info)) {
            throw new \Exception("Db setting element missing", "500");
        }
        
        // Set the array elements into variables
        $dsn = "mysql:host=localhost;dbname=" . $info["dbname"];
        $username = $info["username"];
        $password = $info["password"];
        
        // Set properties
        $this->pdo = parent::__construct($dsn, $username, $password, $options);
        $this->options = $options;
        
    }
}

