<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ==========================================
 * Helper Tanggal (Indonesia)
 * ==========================================
 */

/**
 * Nama bulan Indonesia
 */
function nama_bulan($bulan)
{
    $bulan = (int)$bulan;
    $map = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    return $map[$bulan] ?? '-';
}

/**
 * Format tanggal Indonesia
 * Contoh: 2025-11-12 -> 12 November 2025
 */
function tgl_indo($tanggal, $with_day = false)
{
    if (!$tanggal || $tanggal === '0000-00-00') return '-';

    $time = strtotime($tanggal);
    if (!$time) return '-';

    $hari = date('N', $time);
    $tgl  = date('d', $time);
    $bln  = date('n', $time);
    $thn  = date('Y', $time);

    $namaHari = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu'
    ];

    $hasil = $tgl . ' ' . nama_bulan($bln) . ' ' . $thn;

    if ($with_day) {
        $hasil = $namaHari[$hari] . ', ' . $hasil;
    }

    return $hasil;
}

/**
 * Format bulan + tahun
 * Contoh: (2025, 11) -> November 2025
 */
function bulan_tahun($tahun, $bulan)
{
    return nama_bulan($bulan) . ' ' . (int)$tahun;
}

/**
 * Hitung selisih hari antar tanggal
 */
function selisih_hari($tgl1, $tgl2)
{
    if (!$tgl1 || !$tgl2) return null;

    $d1 = new DateTime($tgl1);
    $d2 = new DateTime($tgl2);
    return (int)$d1->diff($d2)->days;
}

/**
 * Label waktu relatif (human readable)
 * Contoh: 3 hari lalu, Hari ini, 2 bulan lalu
 */
function waktu_relatif($tanggal)
{
    if (!$tanggal) return '-';

    $time = strtotime($tanggal);
    if (!$time) return '-';

    $now = time();
    $diff = $now - $time;

    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    if ($diff < 2592000) return floor($diff / 604800) . ' minggu lalu';
    if ($diff < 31536000) return floor($diff / 2592000) . ' bulan lalu';

    return floor($diff / 31536000) . ' tahun lalu';
}

/**
 * Awal & akhir bulan
 * return array [start, end]
 */
function range_bulan($tahun, $bulan)
{
    $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $start = "$tahun-$bulan-01";
    $end   = date('Y-m-t', strtotime($start));
    return [$start, $end];
}

/**
 * Validasi tanggal (YYYY-MM-DD)
 */
function is_valid_date($tanggal)
{
    if (!$tanggal) return false;
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $d && $d->format('Y-m-d') === $tanggal;
}
