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

        // Ambil daftar tahun unik dari tabel log_minyak untuk filter
        // Jika model belum punya, bisa gunakan query builder langsung di sini
        $years_in_db = $this->db->select('DISTINCT(YEAR(tanggal)) as tahun')
            ->from('log_minyak')
            ->where('motor_id', $motor->id)
            ->order_by('tahun', 'DESC')
            ->get()->result();

        $data['available_years'] = array_map(function ($item) {
            return $item->tahun;
        }, $years_in_db);

        // Pastikan tahun saat ini masuk dalam list jika db kosong
        if (!in_array(date('Y'), $data['available_years'])) {
            array_unshift($data['available_years'], date('Y'));
        }

        $data['filterYear']  = $filterYear;
        $data['filterMonth'] = $filterMonth;
        // Tambahkan variabel ini untuk memperbaiki error JavaScript
        $data['yearNow']     = date('Y');

        $data['minyak_list'] = $this->log_minyak->get_with_stats_by_motor_month($motor->id, $filterYear, $filterMonth, 50);
        $data['motor']        = $motor;
        $data['service_list'] = $this->log_service->get_recent_by_motor($motor->id, 5);
        $data['oli_list']     = $this->log_oli->get_recent_by_motor($motor->id, 5);

        // Ambil daftar merek oli unik untuk saran di form
        $data['oli_brands'] = $this->db->select('DISTINCT(merek_oli)')->from('log_ganti_oli')->get()->result();

        // Ambil semua referensi komponen untuk Maintenance Plan
        $data['ref_komponen'] = $this->db->get('ref_komponen')->result();

        $this->load->view('motor', $data);
    }

    /* ---------- SIMPAN MINYAK ---------- */
    public function simpan_minyak()
    {
        $motor_id = (int)$this->input->post('motor_id');
        $odo_disp = (int)$this->input->post('odo_display_km');

        // 1. Hitung Odo Total
        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'           => $motor_id,
            'tanggal'            => $this->input->post('tanggal'),
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

        $insert = $this->log_minyak->insert($data);

        if ($insert) {
            // 2. UPDATE ODO TERBARU DI TABEL MOTOR (Penting untuk dashboard)
            $this->db->where('id', $motor_id)->update('motor', ['odo_current_km' => $odo_disp]);

            echo json_encode(['ok' => true, 'msg' => 'Data bensin berhasil dicatat']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Gagal menyimpan ke database']);
        }

        return;
    }

    /* ---------- SIMPAN SERVICE ---------- */
    public function simpan_service()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $motor_id = (int)$this->input->post('motor_id');
        $odo_disp = (int)$this->input->post('odo_display_km');
        $komponen_ids = $this->input->post('komponen_ids'); // Ambil array dari checkbox/multi-select
        $tanggal = $this->input->post('tanggal');

        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'       => $motor_id,
            'tanggal'        => $tanggal,
            'odo_display_km' => $odo_disp,
            'odo_total_km'   => $odo_total,
            'harga'          => (int)$this->input->post('harga'),
            'keterangan'     => $this->input->post('keterangan'),
            'lokasi_label'   => $this->input->post('lokasi_label'),
            'latitude'       => $this->input->post('latitude') ? $this->input->post('latitude') : null,
            'longitude'      => $this->input->post('longitude') ? $this->input->post('longitude') : null,
        ];

        $insert = $this->log_service->insert($data);

        if ($insert) {
            // 1. Update Odometer motor
            $this->db->where('id', $motor_id)
                ->set('odo_current_km', "GREATEST(odo_current_km, $odo_disp)", FALSE)
                ->update('motor');

            // 2. TRIGGER UPDATE MAINTENANCE PLAN
            if (!empty($komponen_ids) && is_array($komponen_ids)) {
                foreach ($komponen_ids as $comp_id) {
                    $data_plan = [
                        'last_service_odo'  => $odo_disp,
                        'last_service_date' => $tanggal,
                        'status'            => 'baik' // Reset otomatis ke baik
                    ];

                    $this->db->where(['motor_id' => $motor_id, 'komponen_id' => $comp_id]);
                    $cek = $this->db->get('motor_maintenance_plan')->row();

                    if ($cek) {
                        $this->db->where('id', $cek->id)->update('motor_maintenance_plan', $data_plan);
                    } else {
                        $data_plan['motor_id'] = $motor_id;
                        $data_plan['komponen_id'] = $comp_id;
                        $this->db->insert('motor_maintenance_plan', $data_plan);
                    }
                }
            }
            echo json_encode(['ok' => true, 'msg' => 'Catatan service dan jadwal komponen berhasil diperbarui!']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Gagal menyimpan catatan service.']);
        }
    }

    /* ---------- SIMPAN GANTI OLI ---------- */
    public function simpan_oli()
    {
        // Pastikan hanya menerima request AJAX
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $motor_id = (int)$this->input->post('motor_id');
        $odo_disp = (int)$this->input->post('odo_display_km');

        // 1. Hitung Odo Total menggunakan logic model Anda
        $odo_total = $this->motor->hitung_odo_total($motor_id, $odo_disp);

        $data = [
            'motor_id'       => $motor_id,
            'tanggal'        => $this->input->post('tanggal'),
            'odo_display_km' => $odo_disp,
            'odo_total_km'   => $odo_total,
            'merek_oli'      => $this->input->post('merek_oli'),
            'harga'          => (int)$this->input->post('harga'),
            'keterangan'     => $this->input->post('keterangan'),
            'lokasi_label'   => $this->input->post('lokasi_label'),
            'latitude'       => $this->input->post('latitude') ? $this->input->post('latitude') : null,
            'longitude'      => $this->input->post('longitude') ? $this->input->post('longitude') : null,
        ];

        // 2. Insert ke log_ganti_oli
        $insert = $this->log_oli->insert($data);

        if ($insert) {
            // 1. Update Odometer motor
            $this->db->where('id', $motor_id)
                ->set('odo_current_km', "GREATEST(odo_current_km, $odo_disp)", FALSE)
                ->update('motor');

            // 2. CARI ID KOMPONEN OLI SECARA DINAMIS
            // Kita cari komponen yang kategorinya 'mesin' dan namanya mengandung 'Oli'
            $this->db->select('id');
            $this->db->from('ref_komponen');
            $this->db->where('kategori', 'mesin');
            $this->db->like('nama_komponen', 'Oli');
            $this->db->limit(1);
            $res_komponen = $this->db->get()->row();

            if ($res_komponen) {
                $comp_id = $res_komponen->id;

                $data_plan = [
                    'last_service_odo'  => $odo_disp,
                    'last_service_date' => $this->input->post('tanggal'),
                    'status'            => 'baik'
                ];

                $this->db->where(['motor_id' => $motor_id, 'komponen_id' => $comp_id]);
                $cek = $this->db->get('motor_maintenance_plan')->row();

                if ($cek) {
                    $this->db->where('id', $cek->id)->update('motor_maintenance_plan', $data_plan);
                } else {
                    $data_plan['motor_id'] = $motor_id;
                    $data_plan['komponen_id'] = $comp_id;
                    $this->db->insert('motor_maintenance_plan', $data_plan);
                }
            }

            echo json_encode(['ok' => true, 'msg' => 'Berhasil! Data oli dan rencana perawatan telah sinkron.']);
        }
    }

    public function simpan_maintenance()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $motor_id = (int)$this->input->post('motor_id');
        $komponen_id = (int)$this->input->post('komponen_id');
        $last_odo = (int)$this->input->post('last_service_odo');

        $data = [
            'motor_id'          => $motor_id,
            'komponen_id'       => $komponen_id,
            'last_service_odo'  => $last_odo,
            'last_service_date' => $this->input->post('last_service_date'),
            'status'            => $this->input->post('status')
        ];

        // Cek apakah data untuk komponen ini sudah ada
        $exists = $this->db->get_where('motor_maintenance_plan', [
            'motor_id' => $motor_id,
            'komponen_id' => $komponen_id
        ])->row();

        if ($exists) {
            $this->db->where('id', $exists->id)->update('motor_maintenance_plan', $data);
        } else {
            $this->db->insert('motor_maintenance_plan', $data);
        }

        // Update Odo global motor agar sinkron
        $this->db->where('id', $motor_id)
            ->set('odo_current_km', "GREATEST(odo_current_km, $last_odo)", FALSE)
            ->update('motor');

        echo json_encode(['ok' => true, 'msg' => 'Status kesehatan part telah diperbarui!']);
    }

    public function simpan_ref_komponen()
    {
        $id = $this->input->post('id');
        $data = [
            'nama_komponen'  => $this->input->post('nama_komponen'),
            'interval_km'    => (int)$this->input->post('interval_km'),
            'interval_bulan' => (int)$this->input->post('interval_bulan'),
            'kategori'       => $this->input->post('kategori'),
        ];

        if ($id) {
            $this->db->where('id', $id)->update('ref_komponen', $data);
            $msg = "Komponen berhasil diperbarui!";
        } else {
            $this->db->insert('ref_komponen', $data);
            $msg = "Komponen baru ditambahkan!";
        }

        echo json_encode(['ok' => true, 'msg' => $msg]);
    }

    public function hapus_ref_komponen($id)
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        // Cek apakah komponen sedang digunakan di maintenance_plan
        $used = $this->db->get_where('motor_maintenance_plan', ['komponen_id' => $id])->num_rows();
        if ($used > 0) {
            echo json_encode(['ok' => false, 'msg' => 'Komponen tidak bisa dihapus karena sedang digunakan dalam rencana perawatan.']);
            return;
        }

        if ($this->db->delete('ref_komponen', ['id' => $id])) {
            echo json_encode(['ok' => true, 'msg' => 'Komponen berhasil dihapus.']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Gagal menghapus data.']);
        }
    }

    // Riwayat

    public function get_riwayat_all()
    {
        $motor_id = (int)$this->input->get('motor_id');
        $tahun    = (int)$this->input->get('tahun');
        $bulan    = (int)$this->input->get('bulan');
        $tipe     = $this->input->get('tipe');

        $queries = [];

        // Logic: Jika tipe 'all' atau 'bensin', masukkan query bensin
        if ($tipe == 'all' || $tipe == 'bensin') {
            $queries[] = "(SELECT id, motor_id, tanggal, odo_display_km, total_uang as biaya, 'bensin' as tipe, jenis_bbm as detail, lokasi_label FROM log_minyak WHERE motor_id = $motor_id)";
        }

        if ($tipe == 'all' || $tipe == 'oli') {
            $queries[] = "(SELECT id, motor_id, tanggal, odo_display_km, harga as biaya, 'oli' as tipe, merek_oli as detail, lokasi_label FROM log_ganti_oli WHERE motor_id = $motor_id)";
        }

        if ($tipe == 'all' || $tipe == 'servis') {
            $queries[] = "(SELECT id, motor_id, tanggal, odo_display_km, harga as biaya, 'servis' as tipe, keterangan as detail, lokasi_label FROM log_service WHERE motor_id = $motor_id)";
        }

        $sql = implode(" UNION ALL ", $queries) . " ORDER BY tanggal DESC, odo_display_km DESC";
        $result = $this->db->query($sql)->result();

        // Filter Tahun & Bulan
        if ($tahun > 0) {
            $result = array_filter($result, function ($v) use ($tahun) {
                return date('Y', strtotime($v->tanggal)) == $tahun;
            });
        }
        if ($bulan > 0) {
            $result = array_filter($result, function ($v) use ($bulan) {
                return date('n', strtotime($v->tanggal)) == $bulan;
            });
        }

        echo json_encode(array_values($result));
    }

    // jadwal

    public function get_maintenance_status()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $motor_id = (int)$this->input->get('motor_id');
        $motor = $this->db->get_where('motor', ['id' => $motor_id])->row();
        $current_odo = (int)$motor->odo_current_km;

        $this->db->select('m.*, r.nama_komponen, r.interval_km, r.interval_bulan, r.kategori');
        $this->db->from('motor_maintenance_plan m');
        $this->db->join('ref_komponen r', 'm.komponen_id = r.id');
        $this->db->where('m.motor_id', $motor_id);
        $plans = $this->db->get()->result();

        $today = new DateTime();

        foreach ($plans as $p) {
            $km_terpakai = $current_odo - $p->last_service_odo;
            $p->sisa_km = $p->interval_km - $km_terpakai;
            $p->persen = max(0, min(100, round(($p->sisa_km / $p->interval_km) * 100)));

            // --- LOGIKA ESTIMASI SISA HARI ---
            $tgl_servis = new DateTime($p->last_service_date);
            $diff = $today->diff($tgl_servis);
            $hari_berjalan = $diff->days > 0 ? $diff->days : 1; // Hindari pembagian dengan nol

            // Hitung rata-rata pemakaian KM per hari
            $avg_km_per_day = $km_terpakai / $hari_berjalan;

            if ($avg_km_per_day > 0 && $p->sisa_km > 0) {
                $p->sisa_hari = ceil($p->sisa_km / $avg_km_per_day);
            } else {
                // Jika motor jarang dipakai (0 KM), gunakan estimasi default bulan (interval_bulan)
                $p->sisa_hari = $p->interval_bulan * 30;
            }

            // --- UPDATE STATUS & WARNA ---
            $new_status = 'baik';
            if ($p->sisa_km <= 0 || $p->sisa_hari <= 0) {
                $new_status = 'ganti';
                $p->sisa_hari = 0;
            } elseif ($p->sisa_km <= ($p->interval_km * 0.2) || $p->sisa_hari <= 14) {
                $new_status = 'waspada'; // Waspada jika sisa 20% KM atau sisa 14 hari
            }

            if ($p->status != $new_status) {
                $this->db->where('id', $p->id)->update('motor_maintenance_plan', ['status' => $new_status]);
                $p->status = $new_status;
            }

            $p->status_color = ($p->status == 'ganti') ? 'danger' : (($p->status == 'waspada') ? 'warning' : 'success');
        }

        echo json_encode(['plans' => $plans, 'current_odo' => $current_odo]);
    }

    public function get_dashboard_stats()
    {
        $motor_id = (int)$this->input->get('motor_id');
        $bulan    = (int)$this->input->get('bulan');
        $tahun    = (int)$this->input->get('tahun');

        // 1. Filter Dasar untuk Statistik
        $where = "motor_id = $motor_id";
        if ($tahun > 0) $where .= " AND YEAR(tanggal) = $tahun";
        if ($bulan > 0) $where .= " AND MONTH(tanggal) = $bulan";

        // 2. Hitung Total Biaya
        $total_bensin = $this->db->select_sum('total_uang')->where($where)->get('log_minyak')->row()->total_uang ?? 0;
        $total_oli    = $this->db->select_sum('harga')->where($where)->get('log_ganti_oli')->row()->harga ?? 0;
        $total_servis = $this->db->select_sum('harga')->where($where)->get('log_service')->row()->harga ?? 0;

        // 3. Hitung Efisiensi Nyata
        $total_liter = $this->db->select_sum('isi_liter')->where($where)->get('log_minyak')->row()->isi_liter ?? 0;
        $min_odo = $this->db->select_min('odo_display_km')->where($where)->get('log_minyak')->row()->odo_display_km;
        $max_odo = $this->db->select_max('odo_display_km')->where($where)->get('log_minyak')->row()->odo_display_km;
        $jarak   = ($max_odo > $min_odo) ? ($max_odo - $min_odo) : 0;
        $efisiensi = ($total_liter > 0 && $jarak > 0) ? round($jarak / $total_liter, 1) : 0;

        // 4. Data Kesehatan Motor
        $current_odo = (int)$this->db->get_where('motor', ['id' => $motor_id])->row()->odo_current_km;
        $this->db->select('m.*, r.nama_komponen, r.interval_km');
        $this->db->from('motor_maintenance_plan m');
        $this->db->join('ref_komponen r', 'm.komponen_id = r.id');
        $this->db->where('m.motor_id', $motor_id);
        $plans = $this->db->get()->result();

        $health_data = [];
        foreach ($plans as $p) {
            $km_terpakai = $current_odo - $p->last_service_odo;
            $sisa_km = $p->interval_km - $km_terpakai;
            $persen  = max(0, min(100, round(($sisa_km / $p->interval_km) * 100)));
            $health_data[] = [
                'nama_komponen' => $p->nama_komponen,
                'persen'        => $persen,
                'sisa_km'       => $sisa_km,
                'status_color'  => ($persen <= 20) ? 'danger' : (($persen <= 50) ? 'warning' : 'success')
            ];
        }
        usort($health_data, function ($a, $b) {
            return $a['persen'] - $b['persen'];
        });

        // 5. LOGIKA GRAFIK DINAMIS (Tahun -> Bulan -> Minggu)
        $chart_labels = [];
        $chart_values = [];

        if ($tahun == 0) {
            // Mode: Semua Tahun (Tampilkan tren per tahun)
            $sql_chart = "SELECT YEAR(tanggal) as grp, SUM(biaya) as total FROM (
            SELECT tanggal, total_uang as biaya FROM log_minyak WHERE motor_id = $motor_id
            UNION ALL SELECT tanggal, harga as biaya FROM log_ganti_oli WHERE motor_id = $motor_id
            UNION ALL SELECT tanggal, harga as biaya FROM log_service WHERE motor_id = $motor_id
        ) as combined GROUP BY grp ORDER BY grp ASC";
            $res_chart = $this->db->query($sql_chart)->result();
            foreach ($res_chart as $rc) {
                $chart_labels[] = $rc->grp;
                $chart_values[] = (int)$rc->total;
            }
        } elseif ($bulan == 0) {
            // Mode: Satu Tahun Penuh (Tampilkan tren per bulan Jan-Des)
            $chart_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $chart_values = array_fill(0, 12, 0);
            $sql_chart = "SELECT MONTH(tanggal) as grp, SUM(biaya) as total FROM (
            SELECT tanggal, total_uang as biaya FROM log_minyak WHERE motor_id = $motor_id AND YEAR(tanggal) = $tahun
            UNION ALL SELECT tanggal, harga as biaya FROM log_ganti_oli WHERE motor_id = $motor_id AND YEAR(tanggal) = $tahun
            UNION ALL SELECT tanggal, harga as biaya FROM log_service WHERE motor_id = $motor_id AND YEAR(tanggal) = $tahun
        ) as combined GROUP BY grp";
            $res_chart = $this->db->query($sql_chart)->result();
            foreach ($res_chart as $rc) {
                $chart_values[$rc->grp - 1] = (int)$rc->total;
            }
        } else {
            // Mode: Satu Bulan (Tampilkan tren per minggu W1-W4)
            $chart_labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
            $chart_values = array_fill(0, 4, 0);
            $sql_chart = "SELECT CEIL(DAY(tanggal)/7) as grp, SUM(biaya) as total FROM (
            SELECT tanggal, total_uang as biaya FROM log_minyak WHERE $where
            UNION ALL SELECT tanggal, harga as biaya FROM log_ganti_oli WHERE $where
            UNION ALL SELECT tanggal, harga as biaya FROM log_service WHERE $where
        ) as combined GROUP BY grp";
            $res_chart = $this->db->query($sql_chart)->result();
            foreach ($res_chart as $rc) {
                if ($rc->grp <= 4) $chart_values[$rc->grp - 1] = (int)$rc->total;
            }
        }

        // 6. Riwayat Terakhir
        $sql_recent = "SELECT tanggal, total_uang as biaya, 'bensin' as tipe FROM log_minyak WHERE motor_id = $motor_id
                   UNION ALL SELECT tanggal, harga as biaya, 'oli' as tipe FROM log_ganti_oli WHERE motor_id = $motor_id
                   UNION ALL SELECT tanggal, harga as biaya, 'servis' as tipe FROM log_service WHERE motor_id = $motor_id
                   ORDER BY tanggal DESC LIMIT 3";
        $recent = $this->db->query($sql_recent)->result();

        echo json_encode([
            'total_biaya'  => (int)($total_bensin + $total_oli + $total_servis),
            'efisiensi'    => $efisiensi,
            'odo_total'    => $current_odo,
            'health'       => array_slice($health_data, 0, 2),
            'recent'       => $recent,
            'chart_labels' => $chart_labels,
            'chart_values' => $chart_values
        ]);
    }

    public function update_motor()
    {
        $id = (int)$this->input->post('motor_id');
        $data = [
            'nama_motor'     => $this->input->post('nama_motor'),
            'plat_nomor'     => $this->input->post('plat_nomor'),
            'odo_initial_km' => (int)$this->input->post('odo_initial_km'),
        ];

        $this->db->where('id', $id)->update('motor', $data);
        echo json_encode(['ok' => true, 'msg' => 'Profil motor berhasil diperbarui!']);
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

    public function delete_oli()
    {
        $id = $this->input->post('id');
        $motor_id = $this->input->post('motor_id');

        // Proses hapus
        $this->db->where(['id' => $id, 'motor_id' => $motor_id]);
        if ($this->db->delete('log_ganti_oli')) {
            echo json_encode(['ok' => true, 'msg' => 'Data oli berhasil dihapus']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Gagal menghapus data']);
        }
    }

    public function delete_service()
    {
        $id = $this->input->post('id');
        $motor_id = $this->input->post('motor_id');

        // Proses hapus
        $this->db->where(['id' => $id, 'motor_id' => $motor_id]);
        if ($this->db->delete('log_service')) {
            echo json_encode(['ok' => true, 'msg' => 'Data service berhasil dihapus']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Gagal menghapus data']);
        }
    }
}
