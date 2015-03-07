<?php
class DatabaseList
{
    protected $conn = null;
    protected $col = "";
    protected $from = "";
    protected $where = "";
    protected $order = "";
    protected $group = "";
    protected $limit = 0;
    protected $page = 0;
    protected $params = array();
    
    function __construct(){
        $this->conn =& SC_Query_Ex::getSingletonInstance();
    }
    
    public function filter($col,$value,$condition = "=")
    {
        if($this->where != ""){
            $col = " AND ".$col;
        }
        if($condition !== false){
            $this->where .= $col." ".$condition." ? ";
        }else{
            $this->where .= $col;
        }
        if(is_array($value)){
            foreach($value as $val){
               $this->params[] = $val; 
            }
        }else{
            $this->params[] = $value;
        }
    }
    
    public function orderBy($str)
    {
        if($this->order != ""){
            $this->order .= " , ";
        }
        $this->order .= $str;
    }
    
    public function limit($limit,$page = 0)
    {
        $this->conn->setLimitOffset($limit, $page);
    }
    
    public function get()
    {
        if($this->order != ""){
            $this->conn->setOrder($this->order);
        }
        //echo $this->conn->getSql($this->col, $this->from, $this->where, $this->params);
        //print_r($this->params);
        return $this->conn->select($this->col, $this->from, $this->where, $this->params);
    }
}