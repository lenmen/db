<?php namespace scripts\db;

/**
 * Author: Lennard Moll
 * Licence: GNU GPL 3
 * Last edited 18 July 2015
 */

include('db_conf.php');

use scripts\db\db_config as conf;

class db extends conf 
{
    public $query;
    private $method;
    private $params;
    private $result;
    
    public function __construct($pdo, $options = array()) {
        parent::__construct($pdo, $options);
        
        // assing arrays to the properties
        $this->query = null;
        $this->result = null;
        $this->method = null;
        $this->params = array();
    }
    
   private function arrayExists() {
        if (is_array($this->query)) {
            return true;
        } else {
            return false;
        }
    }
    
    // Void functions 
    public function select($query) {
        $select = "SELECT ";
        
        if(is_array($query)) {
            // query is an array - create a sql string
            foreach ($query as $elem) {
                $select .=  $elem . ", ";
            }
            
            // remove the last ','
            $select = rtrim($select, ", ");
        } else if(is_string($select)) {
            // $query = string
            $select .= $query;
        } else {
            $select .= '*';
        }
        
        // Look if array exists and place the right line 
        if ($this->arrayExists()) {
            $this->query["select"] = $select;
        } else {
            $this->query = array("select" => $select);
        }
    }
    
    public function from ($string) {
        if (!is_string($string) || empty($string)) {
            throw new \Exception("Unknow table");
        }
        
        $from = "FROM " . $string;
        
        if ($this->arrayExists()) {
            $this->query["from"] = $from;
        } else {
            $this->query = array("from" => $from);
        }
    }
    
    public function join ($table, $join, $method) {
        // Validate the fields 
        if (empty($table) || empty($join) || empty($method)) {
            throw new \Exception("Wrong join statement", "500");
        } 
        
        // Create the join
        $join = $method . " JOIN " . $table . " ON " . $join;
        
        if ($this->arrayExists()) {
            // look if there are already joins
            if (array_key_exists($this->query, "joins")) {
                // Element exists
                $this->query["joins"][] = $join;
            } else {
                $this->query["joins"] = array($join);
            }
        } else {
            $this->query = array("joins" => array($join));
        }
    }
    
    public function where ($clauses, $params = null) {
        $where = (is_null($params)) ? "WHERE " : $clauses;
        
        if (is_array($clauses)) {
            foreach ($clauses as $clause) {
                $where .= $clause["where"] . " AND ";
            }
            
            // remove the last and
            rtrim($where, "AND ");
            
            if (!array_key_exists("params", $clauses)) {
                throw new \Exception("Params are missing", "500");
            }
            
            $this->params = $params["params"];
        } else {            
            if (is_null($params) || !is_array($params)) {
                throw new \Exception("Params are missing", "500");
            }
            
            $this->params = $params;
        }
        
        if ($this->arrayExists()) {
            $this->query["where"] = $where;
        } else {
            $this->query = array('where' => $where);
        }
    }
    
    private function createJoinString() {
       $sql  = '';
       // Look if there are joins in the query
       if(array_key_exists("joins", $this->query)) {
           // place the elemetns in the query
           foreach ($this->query["joins"] as $join) {
               $sql .= " " . $join;
           }
       }
       
       return $sql;
    }
    
    private function createWhereString() {
        $where = '';
        
        if(array_key_exists("where", $this->query)) {
            return $this->query["where"];
        } else {
            return $where;
        }
    }
    
    public function get($table = null) {
        // Validation
        if (!array_key_exists("from", $this->query) && is_null($table)) {
            throw new \Exception("unknow table name", "500");
        }
        
        // Variables for the query
        $tbl = (is_null($table)) ? $this->query["from"] : $table;
        $joins = $this->createJoinString();
        $where = $this->createWhereString();
        
        // Create a prepared statement
        $sql = $this->query["select"] . ' ' . $tbl . $joins . ' ' . $where;
        
        try {
            $dbh = parent::prepare($sql);
            

            while($key = current($this->params)) {
                $param = key($this->params);

                $dbh->bindParam($param, $this->params[$param]);

                next($this->params);
            }
            
            // Execute the function
            $dbh->execute();  
            
            if($dbh->rowCount() > 1) {
                $result = $dbh->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                // fetch the result
                $result = $dbh->fetchObject();
            }
            
            return $result;
        } catch (\PDOException $msg) {
            return $msg->getMessage();
        }
    }
}

