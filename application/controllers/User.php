<?php

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        // if (!$this->session->userdata('email')) {
        //     redirect('auth');
        // }
    }
    public function index()
    {
        $data['judul'] = 'My Profile';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // echo 'Selamat datang ' . $data['user']['name'];
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function edit()
    {
        $data['judul'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // echo 'Selamat datang ' . $data['user']['name'];

        $this->form_validation->set_rules('name', 'Name', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');
            $where = [
                'email', $email
            ];
            $set = [
                'name', $name
            ];

            // cek gambar yg diupload
            $upload_image = $_FILES['image']['name'];
            if ($upload_image) {
                $config['upload_path'] = './assets/img/profile/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']     = '2048';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                    $setw = [
                        'image', $new_image
                    ];
                    $this->model->editdata('user', $where, $setw);
                } else {
                    echo $this->upload->display_errors();
                }
            }

            $this->model->editdata('user', $where, $set);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Your profile has been updated! </div>');
            redirect('user');
        }
    }

    public function changepassword()
    {
        $data['judul'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        // echo 'Selamat datang ' . $data['user']['name'];
        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[8]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Repeat Password', 'required|trim|min_length[8]|matches[new_password1]');
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('templates/footer');
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Wrong current password! </div>');
                redirect('user/changepassword');
            } else {
                if ($new_password == $current_password) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            New password cannot be the same as current password </div>');
                    redirect('user/changepassword');
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $where = [
                        'password', $password_hash
                    ];
                    $email = $this->session->userdata('email');
                    $set = [
                        'email', $email
                    ];
                    $this->model->editdata('user', $where, $set);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Your password has been updated </div>');
                    redirect('user/changepassword');
                }
            }
        }
    }
}
