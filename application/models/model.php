<?php

class model extends CI_Model
{
    public function insertDB($data, $table)
    {
        $this->db->insert($table, $data);
    }
    public function editdata($tabel, $where, $set)
    {
        $this->db->set($set[0], $set[1]);
        $this->db->where($where[0], $where[1]);
        $this->db->update($tabel);
    }
    public function data($tabel)
    {
        return $this->db->get($tabel)->result_array();
    }
    public function get_where($tabel, $where)
    {
        return $this->db->get_where($tabel, $where)->row_array();
    }
    public function delete_data($tabel, $where)
    {
        return $this->db->delete($tabel, $where);
    }
    public function menu()
    {
        return $this->db->get('user_menu')->result_array();
    }
    public function getdata()
    {
        return $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    }
    public function getSubMenu()
    {
        $query = " SELECT `user_sub_menu` . * ,`user_menu`.`menu` FROM `user_sub_menu` JOIN `user_menu` ON `user_sub_menu`.`menu_id` = `user_menu`.`id`
        ";
        return $this->db->query($query)->result_array();
    }
    public function getSubData()
    {
        return $this->db->get('user_sub_menu')->result_array();
    }
    public function add()
    {
        $this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);
    }
}
