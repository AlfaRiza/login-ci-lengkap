<?php
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('form_validation');
    }
    public function index()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run() == false) {
            $data['judul'] = 'Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            // validasi
            $this->_login();
        }
    }
    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            // user ada
            if ($user['is_active'] = 1) {
                // jika user aktif
                if (password_verify($password, $user['password'])) {
                    // password benar
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    redirect('user');
                } else {
                    // password salah
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> Wrong password! </div>');
                    redirect('auth');
                }
            } else {
                // tidak aktif
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            This email has not activated! </div>');
                redirect('auth');
            }
        } else {
            // gagal
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Email is not registed! </div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => 'this email has already registed'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[8]|matches[password2]', [
            'matches' => 'Password dont matches!',
            'min_length' => 'Password to short!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password]');

        if ($this->form_validation->run() == false) {

            $data['judul'] = 'Registration Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name'), true),
                'email' => htmlspecialchars($this->input->post('email'), true),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];

            $this->model->insertDB($data, 'user');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Congratulation! your account has been created. Please login! </div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            You have been logged out! </div>');
        redirect('auth');
    }
}
