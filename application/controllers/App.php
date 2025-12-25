<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->library('auth'); // kalau mau pakai

        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $this->load->helper(['url', 'form']);
        $this->load->model('Motor_model', 'motor');
        $this->load->model('Log_minyak_model', 'log_minyak');
        $this->load->model('Log_service_model', 'log_service');
        $this->load->model('Log_ganti_oli_model', 'log_oli');

        $this->load->helper('tanggal');
    }

    public function index()
    {
        $motor = $this->motor->get_default_motor();
        if (!$motor) {
            // minimal buat 1 motor dummy dulu
            // di produksi, sebaiknya via form terpisah
            $this->db->insert('motor', [
                'nama'       => 'Motor Saya',
                'plat_nomor' => 'BM 1234 XX',
                'merk'       => 'Honda',
                'tipe'       => 'Beat',
                'odo_max_km' => 99999,
            ]);
            $motor = $this->motor->get_default_motor();
        }

        $filterYear  = (int)$this->input->get('y');
        $filterMonth = (int)$this->input->get('m');

        if ($filterYear <= 0)  $filterYear  = (int)date('Y');
        if ($filterMonth < 1 || $filterMonth > 12) $filterMonth = (int)date('n');

        $data['filterYear']  = $filterYear;
        $data['filterMonth'] = $filterMonth;

        // minyak list FILTERED (bulan/tahun)
        $data['minyak_list'] = $this->log_minyak->get_with_stats_by_motor_month($motor->id, $filterYear, $filterMonth, 50);

        $data['motor']        = $motor;
        $data['service_list'] = $this->log_service->get_recent_by_motor($motor->id, 5);
        $data['oli_list']     = $this->log_oli->get_recent_by_motor($motor->id, 5);

        $this->load->view('motor', $data);
    }

    /* ---------- SIMPAN MINYAK ---------- */
    public function simpan_minyak()
    {
        $motor_id = (int)$this->input->post('motor_id');
        $tanggal  = $this->input->post('tanggal');
        $odo_disp = (int)$this->input->post('odo_display_km');

        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'           => $motor_id,
            'tanggal'            => $tanggal,
            'odo_display_km'     => $odo_disp,
            'odo_total_km'       => $odo_total,
            'sisa_minyak_batang' => (int)$this->input->post('sisa_minyak_batang'),
            'jenis_bbm'          => $this->input->post('jenis_bbm'),
            'isi_liter'          => $this->input->post('isi_liter'),
            'total_uang'         => (int)$this->input->post('total_uang'),
            'lokasi_label'       => $this->input->post('lokasi_label'),
            'latitude'           => $this->input->post('latitude'),
            'longitude'          => $this->input->post('longitude'),
            'catatan'            => $this->input->post('catatan'),
        ];

        $this->log_minyak->insert($data);

        $this->session->set_flashdata('msg', 'Catatan minyak tersimpan.');
        redirect('motor');
    }

    /* ---------- SIMPAN SERVICE ---------- */
    public function simpan_service()
    {
        $motor_id = (int)$this->input->post('motor_id');
        $tanggal  = $this->input->post('tanggal');
        $odo_disp = (int)$this->input->post('odo_display_km');

        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'       => $motor_id,
            'tanggal'        => $tanggal,
            'odo_display_km' => $odo_disp,
            'odo_total_km'   => $odo_total,
            'harga'          => (int)$this->input->post('harga'),
            'keterangan'     => $this->input->post('keterangan'),
            'lokasi_label'   => $this->input->post('lokasi_label'),
            'latitude'       => $this->input->post('latitude'),
            'longitude'      => $this->input->post('longitude'),
        ];

        $this->log_service->insert($data);

        $this->session->set_flashdata('msg', 'Catatan service tersimpan.');
        redirect('motor');
    }

    /* ---------- SIMPAN GANTI OLI ---------- */
    public function simpan_oli()
    {
        $motor_id = (int)$this->input->post('motor_id');
        $tanggal  = $this->input->post('tanggal');
        $odo_disp = (int)$this->input->post('odo_display_km');

        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'       => $motor_id,
            'tanggal'        => $tanggal,
            'odo_display_km' => $odo_disp,
            'odo_total_km'   => $odo_total,
            'merek_oli'      => $this->input->post('merek_oli'),
            'harga'          => (int)$this->input->post('harga'),
            'keterangan'     => $this->input->post('keterangan'),
            'lokasi_label'   => $this->input->post('lokasi_label'),
            'latitude'       => $this->input->post('latitude'),
            'longitude'      => $this->input->post('longitude'),
        ];

        $this->log_oli->insert($data);

        $this->session->set_flashdata('msg', 'Catatan ganti oli tersimpan.');
        redirect('motor');
    }

    /* ---------- API: ANALITIK BULANAN (JSON) ---------- */
    public function get_analytics()
    {
        $motor_id = (int)$this->input->get('motor_id');
        $tahun    = (int)$this->input->get('tahun');
        $bulan    = (int)$this->input->get('bulan');

        if (!$motor_id || !$tahun || !$bulan) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Parameter tidak lengkap']));
        }

        $data = $this->motor->get_analytics_bulanan($motor_id, $tahun, $bulan);

        return $this->output
            ->set_content_type('application/json')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_output(json_encode($data));
    }

    public function update_minyak()
    {
        $id = (int)$this->input->post('id');
        $motor_id = (int)$this->input->post('motor_id');
        $tanggal  = $this->input->post('tanggal');
        $odo_disp = (int)$this->input->post('odo_display_km');

        if (!$id || !$motor_id || !$tanggal || !$odo_disp) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok' => false, 'msg' => 'Parameter tidak lengkap']));
        }

        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'tanggal'            => $tanggal,
            'odo_display_km'     => $odo_disp,
            'odo_total_km'       => $odo_total,
            'sisa_minyak_batang' => $this->input->post('sisa_minyak_batang') !== '' ? (int)$this->input->post('sisa_minyak_batang') : null,
            'jenis_bbm'          => $this->input->post('jenis_bbm'),
            'isi_liter'          => $this->input->post('isi_liter') !== '' ? $this->input->post('isi_liter') : null,
            'total_uang'         => $this->input->post('total_uang') !== '' ? (int)$this->input->post('total_uang') : null,
            'lokasi_label'       => $this->input->post('lokasi_label'),
            'latitude'           => $this->input->post('latitude') !== '' ? $this->input->post('latitude') : null,
            'longitude'          => $this->input->post('longitude') !== '' ? $this->input->post('longitude') : null,
            'catatan'            => $this->input->post('catatan'),
        ];

        $this->log_minyak->update_by_id($id, $motor_id, $data);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true]));
    }

    public function delete_minyak()
    {
        $id = (int)$this->input->post('id');
        $motor_id = (int)$this->input->post('motor_id');

        if (!$id || !$motor_id) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok' => false, 'msg' => 'Parameter tidak lengkap']));
        }

        $this->log_minyak->delete_by_id($id, $motor_id);

        return $this->output->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true]));
    }
}
