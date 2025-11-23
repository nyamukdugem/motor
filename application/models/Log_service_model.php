<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_service_model extends CI_Model
{
    protected $table = 'log_service';

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_recent_by_motor($motor_id, $limit = 10)
    {
        return $this->db->where('motor_id', $motor_id)
            ->order_by('tanggal', 'DESC')
            ->order_by('id', 'DESC')
            ->limit($limit)
            ->get($this->table)->result();
    }
}
