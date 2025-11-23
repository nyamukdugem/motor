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
}
