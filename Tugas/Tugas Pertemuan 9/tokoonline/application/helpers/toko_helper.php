<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('rupiah'))
{
	function rupiah($angka)
	{
		return 'Rp '.number_format((float) $angka, 0, ',', '.');
	}
}

if ( ! function_exists('status_pesanan'))
{
	/** Daftar status pesanan => [label, warna badge Bootstrap 5, ikon]. */
	function status_pesanan($status = NULL)
	{
		$daftar = [
			'menunggu'   => ['Menunggu Pembayaran', 'warning', 'bi-hourglass-split'],
			'diproses'   => ['Diproses', 'info', 'bi-box-seam'],
			'dikirim'    => ['Dikirim', 'primary', 'bi-truck'],
			'selesai'    => ['Selesai', 'success', 'bi-check-circle'],
			'dibatalkan' => ['Dibatalkan', 'secondary', 'bi-x-circle'],
		];
		return $status === NULL ? $daftar : $daftar[$status];
	}
}

if ( ! function_exists('badge_status'))
{
	function badge_status($status)
	{
		list($label, $warna, $ikon) = status_pesanan($status);
		return '<span class="badge text-bg-'.$warna.'"><i class="bi '.$ikon.'"></i> '.$label.'</span>';
	}
}

if ( ! function_exists('gambar_produk'))
{
	function gambar_produk($file)
	{
		return base_url('upload/produk/'.($file ?: 'default.png'));
	}
}

if ( ! function_exists('bs_input'))
{
	/** Input Bootstrap 5 + pesan error validasi CodeIgniter. */
	function bs_input($name, $label, $default = '', $type = 'text', $extra = '')
	{
		$error = form_error($name, ' ', ' ');
		$value = $type === 'password' ? '' : html_escape(set_value($name, $default));
		if ($type === 'textarea')
		{
			$field = '<textarea class="form-control'.($error ? ' is-invalid' : '').'" id="'.$name.'" name="'.$name.'" '.$extra.'>'.$value.'</textarea>';
		}
		else
		{
			$field = '<input type="'.$type.'" class="form-control'.($error ? ' is-invalid' : '').'" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$extra.'>';
		}
		return '<div class="mb-3"><label class="form-label" for="'.$name.'">'.$label.'</label>'.$field
			.($error ? '<div class="invalid-feedback">'.$error.'</div>' : '').'</div>';
	}
}
