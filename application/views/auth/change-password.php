<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 ">Change Your Password for </h1>
                                    <h5 class="mb-4" <?= $this->session->userdata('reset_email'); ?></h5> </div> <?= $this->session->flashdata('message'); ?> <form class="user" method="post" action="<?= base_url('auth/changepassword'); ?>">
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="password" name="password" autofocus placeholder="Enter New Password" >
                                            <?= form_error('password', '<small class="text-danger">', '</small>'); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="password2" name="password2" autofocus placeholder="Repeat password" >
                                            <?= form_error('password2', '<small class="text-danger">', '</small>'); ?>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Reset Password
                                        </button>
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <a class="small" href="<?= base_url('auth'); ?>">Back to login</a>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>