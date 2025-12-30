<?php
defined('BASEPATH') or exit('No direct script access allowed');

// use PHPGangsta_GoogleAuthenticator;

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Setup TOTP (Google Authenticator)
     * 
     * Rekomendasi:
     * - Hanya bisa diakses saat belum ada secret (is_2fa_enabled = 0)
     * - Atau sementara biarkan, tapi ini gerbang paling sensitif
     */
    public function setup()
    {
        // Ambil data owner
        $owner = $this->db->get_where('app_owner', ['id' => 1])->row();

        if (!$owner) {
            show_error('Owner tidak ditemukan di database.');
        }

        // Kalau sudah aktif, boleh lu redirect aja
        if ($owner->is_2fa_enabled) {
            // Sudah pernah setup, tidak boleh ulang sembarangan
            redirect('auth/login');
            return;
        }

        $ga = new PHPGangsta_GoogleAuthenticator();

        // Kalau belum ada secret, generate baru dan simpan ke DB
        if (empty($owner->totp_secret)) {
            $secret = $ga->createSecret();

            $this->db->where('id', 1)->update('app_owner', [
                'totp_secret' => $secret
            ]);

            $owner->totp_secret = $secret;
        }

        // Buat URL QR Code untuk discan di Google Authenticator
        $appName   = 'MotorKu';   // namain sesuka lu
        $label     = $appName . ':Owner';
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($label, $owner->totp_secret, $appName);

        // Kalau form POST untuk verifikasi OTP pertama kali
        if ($this->input->method() === 'post') {
            $otp = trim($this->input->post('otp'));

            if ($otp === '') {
                $this->session->set_flashdata('error', 'Kode OTP wajib diisi.');
                redirect('auth/setup');
                return;
            }

            $checkResult = $ga->verifyCode($owner->totp_secret, $otp, 2); // toleransi ±60 detik

            if ($checkResult) {
                // Aktifkan 2FA
                $this->db->where('id', 1)->update('app_owner', [
                    'is_2fa_enabled' => 1
                ]);

                $this->session->set_flashdata('success', 'Google Authenticator berhasil diaktifkan. Silakan login dengan OTP.');
                redirect('auth/login');
            } else {
                $this->session->set_flashdata('error', 'Kode OTP salah. Coba lagi.');
                redirect('auth/setup');
            }

            return;
        }

        // Tampilkan view setup
        $data = [
            'qrCodeUrl' => $qrCodeUrl,
            'secret'    => $owner->totp_secret,
        ];

        $this->load->view('auth_setup_totp', $data);
    }

    /**
     * Login hanya pakai OTP dari Google Authenticator
     */
    public function login()
    {
        // Kalau sudah login, arahkan ke dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('app'); // ganti sesuai controller utama lu
            return;
        }

        $owner = $this->db->get_where('app_owner', ['id' => 1])->row();

        if (!$owner || !$owner->is_2fa_enabled || empty($owner->totp_secret)) {
            // Belum setup 2FA, paksa setup dulu
            redirect('auth/setup');
            return;
        }

        if ($this->input->method() === 'post') {
            $otp = trim($this->input->post('otp'));

            if ($otp === '') {
                $this->session->set_flashdata('error', 'Kode OTP wajib diisi.');
                redirect('auth/login');
                return;
            }

            $ga = new PHPGangsta_GoogleAuthenticator();
            $isValid = $ga->verifyCode($owner->totp_secret, $otp, 2);

            if ($isValid) {
                // Set session login
                $this->session->set_userdata([
                    'logged_in' => true,
                    'owner_id'  => $owner->id,
                    'owner_nama' => $owner->nama,
                ]);

                redirect('app'); // arahkan ke halaman utama aplikasi motor
            } else {
                $this->session->set_flashdata('error', 'Kode OTP salah atau sudah kadaluarsa.');
                redirect('auth/login');
            }

            return;
        }

        $this->load->view('auth_login');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
