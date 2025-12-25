<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Motor_model extends CI_Model
{
    protected $table = 'motor';

    public function get_default_motor()
    {
        // sementara ambil motor pertama saja
        return $this->db->order_by('id', 'ASC')->get($this->table)->row();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Ambil odometer terakhir (display & total) dari SEMUA log (minyak, service, oli)
     */
    public function get_last_odo($motor_id)
    {
        $sql = "
            SELECT odo_display_km, odo_total_km, tanggal FROM (
                SELECT odo_display_km, odo_total_km, tanggal
                FROM log_minyak
                WHERE motor_id = ?
                UNION ALL
                SELECT odo_display_km, odo_total_km, tanggal
                FROM log_service
                WHERE motor_id = ?
                UNION ALL
                SELECT odo_display_km, odo_total_km, tanggal
                FROM log_ganti_oli
                WHERE motor_id = ?
            ) AS t
            ORDER BY tanggal DESC, odo_total_km DESC
            LIMIT 1
        ";
        return $this->db->query($sql, [$motor_id, $motor_id, $motor_id])->row();
    }

    /**
     * Hitung odo_total_km baru berdasarkan odo_display_km sekarang
     * dengan memperhitungkan odometer muter di angka max (misal 99.999)
     */
    public function hitung_odo_total($motor_id, $odo_display_km)
    {
        $motor = $this->get_by_id($motor_id);
        $odo_max = $motor ? (int)$motor->odo_max_km : 99999;

        $last = $this->get_last_odo($motor_id);

        if (!$last) {
            // pertama kali catat, anggap total = display
            return (int)$odo_display_km;
        }

        $last_display = (int)$last->odo_display_km;
        $last_total   = (int)$last->odo_total_km;

        if ($odo_display_km >= $last_display) {
            // belum reset, tinggal tambah selisih
            $selisih = $odo_display_km - $last_display;
            return $last_total + $selisih;
        } else {
            // odometer reset, misal dari 99.999 -> 0
            $selisih = ($odo_max - $last_display) + 1 + $odo_display_km;
            return $last_total + $selisih;
        }
    }

    /**
     * Ambil data analitik per bulan
     * return array:
     * [
     *   'label' => 'November 2025',
     *   'total' => 450000,
     *   'minyak' => ['total' => ..., 'count' => ...],
     *   'service' => [...],
     *   'oli' => [...],
     *   'km_start' => 23500,
     *   'km_end'   => 24500,
     * ]
     */
    public function get_analytics_bulanan($motor_id, $tahun, $bulan)
    {
        $bulan = (int)$bulan;
        $bulan2 = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);

        $periode_awal  = sprintf('%04d-%02d-01', (int)$tahun, $bulan);
        $periode_akhir = date("Y-m-t", strtotime($periode_awal));

        // --- helper aman: null -> 0
        $toInt = function ($v) {
            if ($v === null || $v === '') return 0;
            return (int)$v;
        };

        // =========================
        // A) Total & count per kategori
        // =========================
        $qMinyak = $this->db->select('COALESCE(SUM(total_uang),0) AS total, COUNT(*) AS jml', false)
            ->from('log_minyak')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        $qService = $this->db->select('COALESCE(SUM(harga),0) AS total, COUNT(*) AS jml', false)
            ->from('log_service')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        $qOli = $this->db->select('COALESCE(SUM(harga),0) AS total, COUNT(*) AS jml', false)
            ->from('log_ganti_oli')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        $minyak_total  = $toInt($qMinyak->total ?? 0);
        $service_total = $toInt($qService->total ?? 0);
        $oli_total     = $toInt($qOli->total ?? 0);

        $minyak_count  = $toInt($qMinyak->jml ?? 0);
        $service_count = $toInt($qService->jml ?? 0);
        $oli_count     = $toInt($qOli->jml ?? 0);

        $total = $minyak_total + $service_total + $oli_total;
        $total_transaksi = $minyak_count + $service_count + $oli_count;

        // =========================
        // B) KM range bulan itu (union semua log)
        // =========================
        $sqlKm = "
        SELECT odo_total_km, tanggal FROM (
            SELECT odo_total_km, tanggal
            FROM log_minyak WHERE motor_id = ? AND tanggal BETWEEN ? AND ?
            UNION ALL
            SELECT odo_total_km, tanggal
            FROM log_service WHERE motor_id = ? AND tanggal BETWEEN ? AND ?
            UNION ALL
            SELECT odo_total_km, tanggal
            FROM log_ganti_oli WHERE motor_id = ? AND tanggal BETWEEN ? AND ?
        ) AS x
        ORDER BY tanggal ASC, odo_total_km ASC
    ";
        $kmRows = $this->db->query($sqlKm, [
            $motor_id,
            $periode_awal,
            $periode_akhir,
            $motor_id,
            $periode_awal,
            $periode_akhir,
            $motor_id,
            $periode_awal,
            $periode_akhir
        ])->result();

        $km_start = null;
        $km_end   = null;
        $km_used  = 0;

        if (!empty($kmRows)) {
            $km_start = (int)$kmRows[0]->odo_total_km;
            $km_end   = (int)end($kmRows)->odo_total_km;
            $km_used  = max(0, $km_end - $km_start);
        }

        // =========================
        // C) KPI: biaya per KM
        // =========================
        $cost_per_km = ($km_used > 0) ? (int)round($total / $km_used) : null;

        // =========================
        // D) KPI: estimasi efisiensi BBM (KM/L) untuk bulan itu
        //    - ambil log minyak bulan itu urut ASC
        // =========================
        $fuelRows = $this->db->select('id, tanggal, odo_total_km, isi_liter')
            ->from('log_minyak')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->order_by('tanggal', 'ASC')
            ->order_by('id', 'ASC')
            ->get()->result();

        $sum_km_per_l = 0.0;
        $count_km_per_l = 0;

        $prevOdo = null;
        foreach ($fuelRows as $r) {
            $odo = (int)$r->odo_total_km;
            $liter = (float)$r->isi_liter;

            if ($prevOdo !== null && $liter > 0) {
                $jarak = $odo - $prevOdo;
                if ($jarak > 0) {
                    $kmpl = $jarak / $liter;
                    // filter outlier kasar (opsional)
                    if ($kmpl > 1 && $kmpl < 120) {
                        $sum_km_per_l += $kmpl;
                        $count_km_per_l++;
                    }
                }
            }
            $prevOdo = $odo;
        }
        $avg_km_per_l = ($count_km_per_l > 0) ? round($sum_km_per_l / $count_km_per_l, 2) : null;

        // =========================
        // E) Last service & last oli + km sejak terakhir (opsional tapi mantep)
        // =========================
        $lastOdo = $this->get_last_odo($motor_id);
        $odo_now = $lastOdo ? (int)$lastOdo->odo_total_km : null;

        $last_service = $this->db->select('tanggal, odo_total_km, harga, keterangan')
            ->from('log_service')
            ->where('motor_id', $motor_id)
            ->order_by('tanggal', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()->row();

        $last_oli = $this->db->select('tanggal, odo_total_km, harga, merek_oli')
            ->from('log_ganti_oli')
            ->where('motor_id', $motor_id)
            ->order_by('tanggal', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()->row();

        $last_minyak = $this->db->select('tanggal, odo_total_km, total_uang, isi_liter, jenis_bbm')
            ->from('log_minyak')
            ->where('motor_id', $motor_id)
            ->order_by('tanggal', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()->row();

        $km_since_minyak = ($odo_now !== null && $last_minyak)
            ? max(0, $odo_now - (int)$last_minyak->odo_total_km)
            : null;


        $km_since_service = ($odo_now !== null && $last_service) ? max(0, $odo_now - (int)$last_service->odo_total_km) : null;
        $km_since_oli     = ($odo_now !== null && $last_oli) ? max(0, $odo_now - (int)$last_oli->odo_total_km) : null;

        $nama_bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        return [
            'label' => ($nama_bulan[$bulan2] ?? $bulan2) . " " . (int)$tahun,

            'total' => $total,
            'total_transaksi' => $total_transaksi,

            'minyak' => ['total' => $minyak_total, 'count' => $minyak_count],
            'service' => ['total' => $service_total, 'count' => $service_count],
            'oli'    => ['total' => $oli_total,    'count' => $oli_count],

            'km' => [
                'start' => $km_start,
                'end'   => $km_end,
                'used'  => $km_used
            ],

            'kpi' => [
                'cost_per_km' => $cost_per_km,     // Rp per km
                'avg_km_per_l' => $avg_km_per_l     // KM/L (estimasi)
            ],

            'last' => [
                'odo_now' => $odo_now,
                'service' => $last_service ? [
                    'tanggal' => $last_service->tanggal,
                    'odo'     => (int)$last_service->odo_total_km,
                    'harga'   => (int)$last_service->harga,
                    'km_since' => $km_since_service,
                    'ket'     => $last_service->keterangan
                ] : null,
                'oli' => $last_oli ? [
                    'tanggal' => $last_oli->tanggal,
                    'odo'     => (int)$last_oli->odo_total_km,
                    'harga'   => (int)$last_oli->harga,
                    'km_since' => $km_since_oli,
                    'merek'   => $last_oli->merek_oli
                ] : null,
                'minyak' => $last_minyak ? [
                    'tanggal'   => $last_minyak->tanggal,
                    'odo'       => (int)$last_minyak->odo_total_km,
                    'harga'     => (int)$last_minyak->total_uang,
                    'liter'     => (float)$last_minyak->isi_liter,
                    'bbm'       => $last_minyak->jenis_bbm,
                    'km_since'  => $km_since_minyak,
                ] : null,
            ],

            'periode' => [
                'start' => $periode_awal,
                'end'   => $periode_akhir
            ]
        ];
    }
}
