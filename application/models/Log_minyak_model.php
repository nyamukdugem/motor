<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_minyak_model extends CI_Model
{
    protected $table = 'log_minyak';

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_with_stats_by_motor($motor_id, $limit = 20)
    {
        // ambil ASC biar gampang hitung selisih dari sebelumnya
        $rows = $this->db->where('motor_id', $motor_id)
            ->order_by('tanggal', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();

        $result = [];
        $prev = null;

        foreach ($rows as $row) {
            if ($prev) {
                $jarak = (int)$row->odo_total_km - (int)$prev->odo_total_km;
                if ($jarak < 0) $jarak = 0;

                $d1 = new DateTime($prev->tanggal);
                $d2 = new DateTime($row->tanggal);
                $selisih_hari = (int)$d1->diff($d2)->days;

                $row->jarak_km = $jarak;
                $row->selisih_hari = $selisih_hari;
            } else {
                // isi pertama, tidak ada pembanding
                $row->jarak_km = null;
                $row->selisih_hari = null;
            }

            $result[] = $row;
            $prev = $row;
        }

        // kita mau tampilin terbaru dulu → balikkan urutan + limit
        $result = array_reverse($result);
        if ($limit && count($result) > $limit) {
            $result = array_slice($result, 0, $limit);
        }

        return $result;
    }

    public function get_with_stats_by_motor_month($motor_id, $tahun, $bulan, $limit = 50)
    {
        $bulan2 = str_pad((string)(int)$bulan, 2, '0', STR_PAD_LEFT);
        $start = (int)$tahun . '-' . $bulan2 . '-01';
        $end = date('Y-m-t', strtotime($start));

        // Ambil juga 1 data sebelum start untuk hitung jarak entry pertama di bulan itu
        $prevRow = $this->db->where('motor_id', $motor_id)
            ->where('tanggal <', $start)
            ->order_by('tanggal', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)->row();

        // Ambil data dalam bulan itu ASC untuk hitung stats
        $rows = $this->db->where('motor_id', $motor_id)
            ->where('tanggal >=', $start)
            ->where('tanggal <=', $end)
            ->order_by('tanggal', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)->result();

        $result = [];
        $prev = $prevRow; // bisa null

        foreach ($rows as $row) {
            if ($prev) {
                $jarak = (int)$row->odo_total_km - (int)$prev->odo_total_km;
                if ($jarak < 0) $jarak = 0;

                $d1 = new DateTime($prev->tanggal);
                $d2 = new DateTime($row->tanggal);
                $selisih_hari = (int)$d1->diff($d2)->days;

                $row->jarak_km = $jarak;
                $row->selisih_hari = $selisih_hari;
            } else {
                $row->jarak_km = null;
                $row->selisih_hari = null;
            }

            $result[] = $row;
            $prev = $row;
        }

        // balikkan jadi terbaru dulu + limit
        $result = array_reverse($result);
        if ($limit && count($result) > $limit) $result = array_slice($result, 0, $limit);

        return $result;
    }


    public function update_by_id($id, $motor_id, $data)
    {
        $this->db->where('id', (int)$id);
        $this->db->where('motor_id', (int)$motor_id);
        return $this->db->update($this->table, $data);
    }

    public function delete_by_id($id, $motor_id)
    {
        $this->db->where('id', (int)$id);
        $this->db->where('motor_id', (int)$motor_id);
        return $this->db->delete($this->table);
    }
}
