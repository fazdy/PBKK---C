<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesanan_model extends CI_Model
{
	private $table = 'pesanan';

	/**
	 * Buat pesanan dari isi keranjang dalam satu transaksi database.
	 * Stok dikunci (SELECT ... FOR UPDATE) lalu dikurangi; jika ada stok yang
	 * tidak cukup, seluruh transaksi dibatalkan. Mengembalikan kode pesanan atau string error.
	 */
	public function buat($user_id, $data, $items)
	{
		$this->db->trans_begin();

		$subtotal = 0;
		$detail = [];
		foreach ($items as $item)
		{
			$p = $this->db->query('SELECT id, nama, harga, stok FROM produk WHERE id = ? AND aktif = 1 FOR UPDATE', [$item->id])->row();
			if ( ! $p || $p->stok < $item->qty)
			{
				$this->db->trans_rollback();
				return 'Stok '.($p ? $p->nama.' tinggal '.$p->stok : 'produk').' tidak mencukupi. Silakan periksa keranjang.';
			}
			$this->db->set('stok', 'stok - '.(int) $item->qty, FALSE)->where('id', $p->id)->update('produk');
			$detail[] = ['produk_id' => $p->id, 'nama_produk' => $p->nama, 'harga' => $p->harga, 'qty' => $item->qty, 'subtotal' => $p->harga * $item->qty];
			$subtotal += $p->harga * $item->qty;
		}

		$kode = 'KM-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
		$this->db->insert($this->table, $data + [
			'kode'       => $kode,
			'user_id'    => $user_id,
			'subtotal'   => $subtotal,
			'ongkir'     => ONGKIR,
			'total'      => $subtotal + ONGKIR,
			'status'     => 'menunggu',
			'created_at' => date('Y-m-d H:i:s'),
		]);
		$pesanan_id = $this->db->insert_id();
		foreach ($detail as &$d) $d['pesanan_id'] = $pesanan_id;
		$this->db->insert_batch('pesanan_detail', $detail);

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return 'Terjadi kesalahan saat menyimpan pesanan.';
		}
		$this->db->trans_commit();
		return $kode;
	}

	public function by_user($user_id)
	{
		return $this->db->select('pesanan.*, (SELECT SUM(qty) FROM pesanan_detail WHERE pesanan_id = pesanan.id) AS jumlah_barang')
			->where('user_id', $user_id)->order_by('created_at', 'DESC')->get($this->table)->result();
	}

	/** Ambil pesanan berdasarkan kode. Jika $user_id diisi, hanya milik user tersebut. */
	public function by_kode($kode, $user_id = NULL)
	{
		$this->db->select('pesanan.*, users.nama AS nama_akun, users.email')
			->from($this->table)->join('users', 'users.id = pesanan.user_id')
			->where('pesanan.kode', $kode);
		if ($user_id) $this->db->where('pesanan.user_id', $user_id);
		$pesanan = $this->db->get()->row();
		if ($pesanan)
		{
			$pesanan->items = $this->db->select('pesanan_detail.*, produk.gambar, produk.slug')
				->from('pesanan_detail')->join('produk', 'produk.id = pesanan_detail.produk_id', 'left')
				->where('pesanan_id', $pesanan->id)->get()->result();
		}
		return $pesanan;
	}

	public function semua($status = NULL)
	{
		$this->db->select('pesanan.*, users.nama AS pelanggan, (SELECT SUM(qty) FROM pesanan_detail WHERE pesanan_id = pesanan.id) AS jumlah_barang')
			->from($this->table)->join('users', 'users.id = pesanan.user_id');
		if ($status) $this->db->where('pesanan.status', $status);
		return $this->db->order_by('pesanan.created_at', 'DESC')->get()->result();
	}

	public function jumlah_per_status()
	{
		$rows = $this->db->select('status, COUNT(*) AS jumlah')->group_by('status')->get($this->table)->result();
		return array_column($rows, 'jumlah', 'status');
	}

	/** Ubah status. Jika dibatalkan, stok produk dikembalikan. */
	public function ubah_status($pesanan, $status)
	{
		if ($pesanan->status === $status) return TRUE;
		if ($pesanan->status === 'dibatalkan') return FALSE; // pesanan batal tidak bisa dihidupkan lagi

		$this->db->trans_start();
		if ($status === 'dibatalkan')
		{
			foreach ($this->db->get_where('pesanan_detail', ['pesanan_id' => $pesanan->id])->result() as $d)
			{
				if ($d->produk_id) $this->db->set('stok', 'stok + '.(int) $d->qty, FALSE)->where('id', $d->produk_id)->update('produk');
			}
		}
		$this->db->update($this->table, ['status' => $status], ['id' => $pesanan->id]);
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	/* ---------- Statistik dashboard admin ---------- */

	public function omzet()
	{
		return (int) $this->db->select_sum('total')->where_in('status', ['diproses', 'dikirim', 'selesai'])->get($this->table)->row()->total;
	}

	/** Total penjualan per hari selama 7 hari terakhir (hari tanpa transaksi = 0). */
	public function penjualan_7_hari()
	{
		$rows = $this->db->select('DATE(created_at) AS tanggal, SUM(total) AS total', FALSE)
			->where('created_at >=', date('Y-m-d', strtotime('-6 days')))
			->where('status <>', 'dibatalkan')
			->group_by('DATE(created_at)')->get($this->table)->result();
		$per_tanggal = array_column($rows, 'total', 'tanggal');

		$hasil = [];
		for ($i = 6; $i >= 0; $i--)
		{
			$tgl = date('Y-m-d', strtotime("-$i days"));
			$hasil[date('d/m', strtotime($tgl))] = isset($per_tanggal[$tgl]) ? (int) $per_tanggal[$tgl] : 0;
		}
		return $hasil;
	}

	public function terlaris($limit = 5)
	{
		return $this->db->select('nama_produk, SUM(qty) AS qty, SUM(pesanan_detail.subtotal) AS omzet')
			->from('pesanan_detail')->join('pesanan', 'pesanan.id = pesanan_detail.pesanan_id')
			->where('pesanan.status <>', 'dibatalkan')
			->group_by('nama_produk')->order_by('qty', 'DESC')->limit($limit)->get()->result();
	}
}
