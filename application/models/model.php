<?php

class model extends CI_Model
{
    public function insertDB($data, $table)
    {
        $this->db->insert($table, $data);
    }
}
