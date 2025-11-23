<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory; // pastikan PhpSpreadsheet sudah terinstall

class Migrate_minyak extends CI_Controller
{
    private $motor_id = 1; // sesuaikan kalau punya lebih dari 1 motor

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // $this->load->library('auth'); // kalau mau pakai auth, silakan aktifkan
    }

    public function index()
    {
        // === 1) SETUP LOKASI FILE ===
        $filePath = FCPATH . 'uploads/minyak.xlsx'; // taruh minyak.xlsx di /uploads
        if (!file_exists($filePath)) {
            show_error('File tidak ditemukan: ' . $filePath);
        }

        // === 2) BACA EXCEL ===
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheet(0);
        $highestRow  = $sheet->getHighestRow();

        // mapping nama bulan Indonesia -> angka
        $bulanMap = [
            'januari'   => 1,
            'februari'  => 2,
            'maret'     => 3,
            'april'     => 4,
            'mei'       => 5,
            'juni'      => 6,
            'juli'      => 7,
            'agustus'   => 8,
            'september' => 9,
            'oktober'   => 10,
            'november'  => 11,
            'desember'  => 12,
        ];

        // Kita tampung dulu semua baris mentah
        $rows = [];

        // Asumsi baris 1 = header
        for ($row = 2; $row <= $highestRow; $row++) {
            $tanggalStr     = trim((string) $sheet->getCell('A' . $row)->getValue()); // Tanggal
            $km             = $sheet->getCell('B' . $row)->getCalculatedValue();      // Kilometer
            $jarakTempuh    = $sheet->getCell('C' . $row)->getCalculatedValue();      // Jarak Tempuh (KM) (opsional)
            $rataRataKm     = $sheet->getCell('D' . $row)->getCalculatedValue();      // Rata-Rata/KM (opsional)
            $hari           = $sheet->getCell('E' . $row)->getCalculatedValue();      // Hari (opsional)
            $sisaBatang     = $sheet->getCell('F' . $row)->getCalculatedValue();      // Sisa (Batang)
            $isiRp          = $sheet->getCell('G' . $row)->getCalculatedValue();      // Isi (Rp)
            $liter          = $sheet->getCell('H' . $row)->getCalculatedValue();      // Liter
            $jenisBbm       = trim((string) $sheet->getCell('I' . $row)->getValue()); // Jenis BBM

            // parse tanggal "Selasa, 03 Januari 2023" -> 2023-01-03
            $tanggalDate = null;
            if ($tanggalStr !== '') {
                $parts = explode(',', $tanggalStr);
                $tgl2  = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]); // ambil bagian setelah koma kalau ada
                // contoh: "03 Januari 2023"
                if (preg_match('~^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$~', $tgl2, $m)) {
                    $d    = (int) $m[1];
                    $bln  = strtolower($m[2]);
                    $y    = (int) $m[3];
                    $mon  = isset($bulanMap[$bln]) ? $bulanMap[$bln] : 1;
                    $tanggalDate = sprintf('%04d-%02d-%02d', $y, $mon, $d);
                }
            }

            $rows[] = [
                'row'           => $row,
                'tanggal_str'   => $tanggalStr,
                'tanggal'       => $tanggalDate,
                'km'            => is_null($km) || $km === '' ? null : (float) $km,
                'jarak_excel'   => $jarakTempuh,
                'rata_rata_km'  => $rataRataKm,
                'hari_excel'    => $hari,
                'sisa_batang'   => is_null($sisaBatang) || $sisaBatang === '' ? null : (int) $sisaBatang,
                'isi_rp'        => is_null($isiRp) || $isiRp === '' ? null : (int) $isiRp,
                'liter'         => is_null($liter) || $liter === '' ? null : (float) $liter,
                'jenis_bbm'     => $jenisBbm !== '' ? $jenisBbm : null,
            ];
        }

        // === 3) HITUNG odo_total_km & odo_display_km (per algoritma reset) ===
        $offset = 0.0;
        $lastKm = null;

        foreach ($rows as $i => $r) {
            $km = $r['km'];

            if ($km === null) {
                // tidak ada km, tidak mengubah offset & lastKm
                $rows[$i]['odo_total_km']   = null;
                $rows[$i]['odo_display_km'] = null;
                continue;
            }

            if ($lastKm !== null && $km < $lastKm) {
                // deteksi reset km (contoh 100049 -> 227)
                $offset += $lastKm;
            }

            $odoTotal   = $offset + $km;
            $kmInt      = (int) round($km);
            $odoDisplay = $kmInt > 99999 ? ($kmInt % 100000) : $kmInt;

            $rows[$i]['odo_total_km']   = (int) round($odoTotal);
            $rows[$i]['odo_display_km'] = $odoDisplay;

            $lastKm = $km;
        }

        // === 4) INSERT ke log_minyak (hanya baris yang ada "Isi (Rp)") ===
        $this->db->trans_start();

        // Opsional: kosongkan dulu tabel log_minyak
        // WARNING: ini hapus semua data minyak yang ada
        // $this->db->truncate('log_minyak');

        $now = date('Y-m-d H:i:s');
        $inserted = 0;

        foreach ($rows as $r) {
            // hanya baris yang ada pengisian uang
            if ($r['isi_rp'] === null) {
                continue;
            }
            if ($r['tanggal'] === null) {
                continue; // aman saja
            }

            $data = [
                'motor_id'           => $this->motor_id,
                'tanggal'            => $r['tanggal'],              // DATE
                'odo_display_km'     => $r['odo_display_km'] ?? 0,  // int
                'odo_total_km'       => $r['odo_total_km'] ?? 0,    // int
                'sisa_minyak_batang' => $r['sisa_batang'],
                'jenis_bbm'          => $r['jenis_bbm'],
                'isi_liter'          => $r['liter'],
                'total_uang'         => $r['isi_rp'],
                'harga_per_liter'    => null,
                'lokasi_label'       => null,
                'latitude'           => null,
                'longitude'          => null,
                'catatan'            => null,
                'created_at'         => $r['tanggal'] . ' 00:00:00',
                'updated_at'         => $r['tanggal'] . ' 00:00:00',
            ];

            $this->db->insert('log_minyak', $data);
            $inserted++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            show_error('Migrasi gagal. Transaction rollback.');
        }

        echo "Migrasi selesai. Baris dimasukkan ke log_minyak: {$inserted}";
    }
}
