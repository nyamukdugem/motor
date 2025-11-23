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
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $periode_awal = "$tahun-$bulan-01";
        $periode_akhir = date("Y-m-t", strtotime($periode_awal)); // last day of month

        // Total & count tiap jenis
        // MINYAK
        $qMinyak = $this->db->select('SUM(total_uang) AS total, COUNT(*) AS jml')
            ->from('log_minyak')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        // SERVICE
        $qService = $this->db->select('SUM(harga) AS total, COUNT(*) AS jml')
            ->from('log_service')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        // GANTI OLI
        $qOli = $this->db->select('SUM(harga) AS total, COUNT(*) AS jml')
            ->from('log_ganti_oli')
            ->where('motor_id', $motor_id)
            ->where('tanggal >=', $periode_awal)
            ->where('tanggal <=', $periode_akhir)
            ->get()->row();

        $total = (int)$qMinyak->total + (int)$qService->total + (int)$qOli->total;

        // Jarak tempuh (km_start & km_end dari periode ini)
        // kita ambil dari UNION semua log
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
        if ($kmRows) {
            $km_start = (int)$kmRows[0]->odo_total_km;
            $km_end   = (int)end($kmRows)->odo_total_km;
        }

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
            'label'   => ($nama_bulan[$bulan] ?? $bulan) . " " . $tahun,
            'total'   => $total,
            'minyak'  => [
                'total' => (int)$qMinyak->total,
                'count' => (int)$qMinyak->jml
            ],
            'service' => [
                'total' => (int)$qService->total,
                'count' => (int)$qService->jml
            ],
            'oli'     => [
                'total' => (int)$qOli->total,
                'count' => (int)$qOli->jml
            ],
            'km_start' => $km_start,
            'km_end'   => $km_end,
        ];
    }
}
