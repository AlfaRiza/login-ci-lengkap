<?php

class Menu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // if (!$this->session->userdata('email')) {
        //     redirect('auth');
        // }
        is_logged_in();
    }
    public function index()
    {
        $data['judul'] = 'Menu Management';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['menu'] = $this->model->data('user_menu');
        // echo 'Selamat datang ' . $data['user']['name'];
        // form validation
        $this->form_validation->set_rules('menu', 'Menu', 'required');
        if ($this->form_validation->run() == false) {
            $data['menu'] = $this->model->menu();
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('templates/footer');
        } else {
            $data = ['menu' => $this->input->post('menu')];
            $this->model->insertDB($data, 'user_menu');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            New menu added </div>');
            redirect('menu');
        }
    }

    public function submenu()
    {
        $data['judul'] = 'Sub Menu Management';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();


        $data['subMenu'] =  $this->model->getSubMenu();
        $data['menu'] = $this->model->data('user_menu');

        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('menu_id', 'Menu', 'required');
        $this->form_validation->set_rules('url', 'url', 'required');
        $this->form_validation->set_rules('icon', 'icon', 'required');
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/submenu', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'title' => $this->input->post('title'),
                'menu_id' => $this->input->post('menu_id'),
                'url' => $this->input->post('url'),
                'icon' => $this->input->post('icon'),
                'is_active' => $this->input->post('is_active')
            ];
            $this->model->insertDB($data, 'user_sub_menu');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            New sub menu added </div>');
            redirect('menu/submenu');
        }
    }
}
