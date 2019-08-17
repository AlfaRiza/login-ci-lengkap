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
        if ($this->session->userdata('email')) {
            redirect('user');
        }
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
            if ($user['is_active'] == 1) {
                // jika user aktif
                if (password_verify($password, $user['password'])) {
                    // password benar
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
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
        if ($this->session->userdata('email')) {
            redirect('user');
        }
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
            $email = $this->input->post('email', true);
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($email),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];

            // siapkan token
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $email,
                'token' => $token,
                'date_created' => time()
            ];

            $this->model->insertDB($data, 'user');
            $this->model->insertDB($user_token, 'user_token');
            // kirim email
            $this->_sendEmail($token, 'verify');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Congratulation! please activate your account. Please login! </div>');
            redirect('auth');
        }
    }

    private function _sendEmail($token, $type)
    {
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'alfahmada45@gmail.com',
            'smtp_pass' => 'plendungan',
            'smtp_port' => 465,
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        //$this->load->library('email', $config);
        $this->email->initialize($config);  //tambahkan baris ini
        $this->email->from('alfahmada45@gmail.com', 'Web Alfa');
        $this->email->to($this->input->post('email'));
        if ($type == 'verify') {
            $this->email->subject('Account verification');
            $this->email->message('Click this link to verify your account : <a href="' . base_url('auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token)) . '">Activate</a>');
        } elseif ($type == 'forgot') {
            $this->email->subject('Reset Password');
            $this->email->message('Click this link to reset your password : <a href="' . base_url('auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token)) . '">Reset Password</a>');
        }
        if ($this->email->send()) {
            return true;
        } else {
            echo 'gagal ' . $this->email->print_debugger();
            die;
        }
    }

    public function verify()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        //$where = [];
        $user = $this->model->get_where('user', ['email' => $email]);

        if ($user) {
            $user_token = $this->model->get_where('user_token', ['token' => $token]);

            if ($user_token) {
                if (time() - $user['date_created'] < (60 * 60 * 24)) {

                    $this->db->set('is_active', 1);
                    $this->db->where('email', $email);
                    $this->db->update('user');
                    $this->model->delete_data('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $email .  ' has been activated! please login </div>');
                    redirect('auth');
                } else {
                    $this->model->delete_data('user', ['email' => $email]);
                    $this->model->delete_data('user_token', ['email' => $email]);
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Acount activation failed! token expired </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Acount activation failed! token invalid </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Acount activation failed! wrong email </div>');
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

    public function blocked()
    {
        $this->load->view('auth/blocked');
    }

    public function forgotPassword()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        if ($this->form_validation->run() == false) {
            $data['judul'] = 'Forgot Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/forgot-password');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('email');
            $user = $this->model->get_where('user', ['email' => $email, 'is_active' => 1]);
            if ($user) {
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];
                $this->model->insertDB($user_token, 'user_token');
                $this->_sendEmail($token, 'forgot');
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Please your email to reset your password </div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Email has not registed! or activated! </div>');
                redirect('auth/forgotpassword');
            }
        }
    }

    public function resetPassword()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->model->get_where('user', ['email' => $email]);

        if ($user) {
            $user_token = $this->model->get_where('user_token', ['token' => $token]);
            if ($user_token) {
                $this->session->set_userdata('reset_email', $email);
                $this->changePassword();
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Reset password failed! invalid token </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Reset password failed! wrong email </div>');
            redirect('auth');
        }
    }

    public function changePassword()
    {
        if(!$this->session->userdata('reset_email')){
            redirect('auth');
        }

        $this->form_validation->set_rules('password','Password','required|trim|min_length[8]|matches[passwprd2]');
        $this->form_validation->set_rules('password','Password','required|trim|min_length[8]|matches[passwprd]');
        if($this->form_validation->run() == false){
            $data['judul'] = 'Change Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/change-password');
            $this->load->view('templates/auth_footer');
        }else{
            $password = password_hash($this->input->post('password'),PASSWORD_DEFAULT);
            $email = $this->session->userdata('reset_email');
            $this->model->editdata('user',['email' => $email],['password' => $password]);

            $this->session->unset_userdata('reset_email');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Password has been change! please login </div>');
            redirect('auth');
        }
    }
}
