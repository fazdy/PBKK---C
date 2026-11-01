<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelas_model extends MY_Model
{
	protected $table = 'kelas';

	/** Query dasar: kelas + mata kuliah + dosen + jumlah peserta. */
	private function _base()
	{
		return $this->db->select('kelas.*, matakuliah.kode, matakuliah.nama AS matakuliah, matakuliah.sks, matakuliah.semester,
				dosen.nama AS dosen, (SELECT COUNT(*) FROM krs WHERE krs.kelas_id = kelas.id) AS peserta')
			->from($this->table)
			->join('matakuliah', 'matakuliah.id = kelas.matakuliah_id')
			->join('dosen', 'dosen.id = kelas.dosen_id');
	}

	public function get_all_lengkap($tahun_ajaran = NULL)
	{
		$this->_base();
		if ($tahun_ajaran) $this->db->where('kelas.tahun_ajaran', $tahun_ajaran);
		return $this->db->order_by("FIELD(kelas.hari,'Senin','Selasa','Rabu','Kamis','Jumat')", '', FALSE)
			->order_by('kelas.jam_mulai')->get()->result();
	}

	public function get_lengkap($id)
	{
		return $this->_base()->where('kelas.id', $id)->get()->row();
	}

	public function get_by_dosen($dosen_id)
	{
		return $this->_base()->where('kelas.dosen_id', $dosen_id)
			->order_by('kelas.tahun_ajaran', 'DESC')
			->order_by("FIELD(kelas.hari,'Senin','Selasa','Rabu','Kamis','Jumat')", '', FALSE)
			->get()->result();
	}

	public function daftar_tahun_ajaran()
	{
		$rows = $this->db->distinct()->select('tahun_ajaran')->order_by('tahun_ajaran', 'DESC')->get($this->table)->result();
		return array_column($rows, 'tahun_ajaran', 'tahun_ajaran');
	}

	/** Peserta kelas beserta nilainya. */
	public function peserta($kelas_id)
	{
		return $this->db->select('krs.id AS krs_id, krs.nilai_angka, krs.nilai_huruf, mahasiswa.nrp, mahasiswa.nama, prodi.kode AS prodi')
			->from('krs')
			->join('mahasiswa', 'mahasiswa.id = krs.mahasiswa_id')
			->join('prodi', 'prodi.id = mahasiswa.prodi_id')
			->where('krs.kelas_id', $kelas_id)
			->order_by('mahasiswa.nrp')
			->get()->result();
	}

	/** Cek bentrok jadwal ruang atau dosen dengan kelas lain di tahun ajaran yang sama. */
	public function bentrok($data, $kecuali_id = NULL)
	{
		$this->db->from($this->table)
			->where('tahun_ajaran', $data['tahun_ajaran'])
			->where('hari', $data['hari'])
			->where('jam_mulai <', $data['jam_selesai'])
			->where('jam_selesai >', $data['jam_mulai'])
			->group_start()
				->where('ruang', $data['ruang'])
				->or_where('dosen_id', $data['dosen_id'])
			->group_end();
		if ($kecuali_id) $this->db->where('id !=', $kecuali_id);
		return $this->db->get()->row();
	}
}
