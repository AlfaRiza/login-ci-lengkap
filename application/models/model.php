<?php

class model extends CI_Model
{
    public function insertDB($data, $table)
    {
        $this->db->insert($table, $data);
    }
    public function menu()
    {
        return $this->db->get('user_menu')->result_array();
    }
    public function getdata()
    {
        return $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    }
    public function getsubdata()
    {
        return $this->db->get('user_sub_menu')->result_array();
    }
    public function add()
    {
        $this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);
    }
}
