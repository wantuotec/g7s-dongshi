<?php

class Search_model extends CI_Model
{
    protected $_error;
    
    function __construct()
    {
        parent::__construct();
    }

    public function browse($domain, $params = array(), $offset = 0, $size = 0, $group_by = 0)
    {

        $this->load->dao($domain);
        if(FALSE === $infos = $this->$domain->browse($params, $offset, $size)){
            $this->_error = $this->$domain->get_error();
            return false;
        }
        
        if (count($infos) < 1) {
            return array(array(), 0);
        }
        
        if(FALSE === $count = $this->$domain->count()){
            $this->_error = $this->$domain->get_error();
            return false;
        }

        return array($infos, $count);
    }
    
    /* 返回错误 */
    public function get_error()
    {
        return $this->_error;
    }
}
