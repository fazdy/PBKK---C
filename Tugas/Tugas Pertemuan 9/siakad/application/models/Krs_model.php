<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Krs_model extends MY_Model
{
	protected $table = 'krs';

	/** KRS mahasiswa pada satu tahun ajaran (lengkap dengan jadwal). */
	public function get_by_mahasiswa($mahasiswa_id, $tahun_ajaran)
	{
		return $this->db->select('krs.*, kelas.nama_kelas, kelas.hari, kelas.jam_mulai, kelas.jam_selesai, kelas.ruang,
				matakuliah.kode, matakuliah.nama AS matakuliah, matakuliah.sks, dosen.nama AS dosen')
			->from($this->table)
			->join('kelas', 'kelas.id = krs.kelas_id')
			->join('matakuliah', 'matakuliah.id = kelas.matakuliah_id')
			->join('dosen', 'dosen.id = kelas.dosen_id')
			->where('krs.mahasiswa_id', $mahasiswa_id)
			->where('kelas.tahun_ajaran', $tahun_ajaran)
			->order_by("FIELD(kelas.hari,'Senin','Selasa','Rabu','Kamis','Jumat')", '', FALSE)
			->order_by('kelas.jam_mulai')
			->get()->result();
	}

	public function total_sks($mahasiswa_id, $tahun_ajaran)
	{
		return array_sum(array_column($this->get_by_mahasiswa($mahasiswa_id, $tahun_ajaran), 'sks'));
	}

	/**
	 * Ambil kelas untuk KRS. Mengembalikan TRUE jika berhasil,
	 * atau string pesan kesalahan jika melanggar aturan KRS.
	 */
	public function ambil($mahasiswa_id, $kelas_id)
	{
		$kelas = $this->kelas_model->get_lengkap($kelas_id);
		if ( ! $kelas || $kelas->tahun_ajaran !== TA_AKTIF) return 'Kelas tidak tersedia pada tahun ajaran ini.';

		$diambil = $this->get_by_mahasiswa($mahasiswa_id, TA_AKTIF);
		foreach ($diambil as $krs)
		{
			if ($krs->kelas_id == $kelas_id) return 'Kelas ini sudah ada di KRS Anda.';
			if ($krs->kode === $kelas->kode) return 'Mata kuliah '.$kelas->matakuliah.' sudah diambil di kelas '.$krs->nama_kelas.'.';
			if ($krs->hari === $kelas->hari && $krs->jam_mulai < $kelas->jam_selesai && $krs->jam_selesai > $kelas->jam_mulai)
			{
				return 'Jadwal bentrok dengan '.$krs->matakuliah.' ('.jadwal($krs).').';
			}
		}

		if ($kelas->peserta >= $kelas->kuota) return 'Kuota kelas '.$kelas->matakuliah.' '.$kelas->nama_kelas.' sudah penuh.';

		$sks = array_sum(array_column($diambil, 'sks')) + $kelas->sks;
		if ($sks > MAX_SKS) return 'Total SKS akan menjadi '.$sks.', melebihi batas '.MAX_SKS.' SKS.';

		$this->insert(['mahasiswa_id' => $mahasiswa_id, 'kelas_id' => $kelas_id]);
		return TRUE;
	}

	/** Batalkan kelas di KRS, hanya untuk TA aktif dan yang belum dinilai. */
	public function batal($mahasiswa_id, $krs_id)
	{
		$krs = $this->db->select('krs.*, kelas.tahun_ajaran')->from($this->table)
			->join('kelas', 'kelas.id = krs.kelas_id')
			->where(['krs.id' => $krs_id, 'krs.mahasiswa_id' => $mahasiswa_id])
			->get()->row();
		if ( ! $krs || $krs->tahun_ajaran !== TA_AKTIF || $krs->nilai_huruf !== NULL) return FALSE;
		return $this->delete($krs_id);
	}

	public function set_nilai($krs_id, $kelas_id, $angka)
	{
		$angka = ($angka === '' || $angka === NULL) ? NULL : (float) $angka;
		return $this->db->where(['id' => $krs_id, 'kelas_id' => $kelas_id])
			->update($this->table, ['nilai_angka' => $angka, 'nilai_huruf' => nilai_huruf($angka)]);
	}

	/**
	 * Kartu Hasil Studi: nilai dikelompokkan per tahun ajaran, lengkap dengan IP per semester dan IPK.
	 */
	public function khs($mahasiswa_id)
	{
		$rows = $this->db->select('krs.nilai_angka, krs.nilai_huruf, kelas.tahun_ajaran, kelas.nama_kelas,
				matakuliah.kode, matakuliah.nama AS matakuliah, matakuliah.sks')
			->from($this->table)
			->join('kelas', 'kelas.id = krs.kelas_id')
			->join('matakuliah', 'matakuliah.id = kelas.matakuliah_id')
			->where('krs.mahasiswa_id', $mahasiswa_id)
			->order_by('kelas.tahun_ajaran', 'DESC')
			->order_by('matakuliah.kode')
			->get()->result();

		$semester = [];
		$total_sks = 0;
		$total_mutu = 0;
		foreach ($rows as $row)
		{
			$ta = $row->tahun_ajaran;
			if ( ! isset($semester[$ta])) $semester[$ta] = ['nilai' => [], 'sks' => 0, 'mutu' => 0, 'sks_dinilai' => 0];
			$semester[$ta]['nilai'][] = $row;
			$semester[$ta]['sks'] += $row->sks;

			$bobot = bobot_nilai($row->nilai_huruf);
			if ($bobot !== NULL)
			{
				$semester[$ta]['sks_dinilai'] += $row->sks;
				$semester[$ta]['mutu'] += $bobot * $row->sks;
				$total_sks += $row->sks;
				$total_mutu += $bobot * $row->sks;
			}
		}
		foreach ($semester as &$s)
		{
			$s['ip'] = $s['sks_dinilai'] ? $s['mutu'] / $s['sks_dinilai'] : NULL;
		}

		return [
			'semester'  => $semester,
			'total_sks' => $total_sks,
			'ipk'       => $total_sks ? $total_mutu / $total_sks : 0,
		];
	}

	/** Jumlah peserta yang belum dinilai di kelas-kelas dosen pada TA aktif. */
	public function belum_dinilai_dosen($dosen_id)
	{
		return $this->db->from($this->table)
			->join('kelas', 'kelas.id = krs.kelas_id')
			->where(['kelas.dosen_id' => $dosen_id, 'kelas.tahun_ajaran' => TA_AKTIF])
			->where('krs.nilai_huruf IS NULL', NULL, FALSE)
			->count_all_results();
	}
}
