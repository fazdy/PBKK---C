<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper akademik: konversi nilai, bobot, dan format jadwal.
 * Dimuat otomatis lewat $autoload['helper'].
 */

if ( ! function_exists('nilai_huruf'))
{
	/** Konversi nilai angka (0-100) ke nilai huruf. */
	function nilai_huruf($angka)
	{
		if ($angka === NULL || $angka === '') return NULL;
		$angka = (float) $angka;
		if ($angka >= 86) return 'A';
		if ($angka >= 76) return 'AB';
		if ($angka >= 66) return 'B';
		if ($angka >= 61) return 'BC';
		if ($angka >= 56) return 'C';
		if ($angka >= 41) return 'D';
		return 'E';
	}
}

if ( ! function_exists('bobot_nilai'))
{
	/** Bobot nilai huruf untuk perhitungan IP/IPK. */
	function bobot_nilai($huruf)
	{
		$bobot = ['A' => 4.0, 'AB' => 3.5, 'B' => 3.0, 'BC' => 2.5, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0];
		return isset($bobot[$huruf]) ? $bobot[$huruf] : NULL;
	}
}

if ( ! function_exists('daftar_hari'))
{
	function daftar_hari()
	{
		return ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat'];
	}
}

if ( ! function_exists('jadwal'))
{
	/** Contoh: "Senin, 07:00 - 09:30" */
	function jadwal($kelas)
	{
		return $kelas->hari.', '.substr($kelas->jam_mulai, 0, 5).' - '.substr($kelas->jam_selesai, 0, 5);
	}
}

if ( ! function_exists('badge_nilai'))
{
	function badge_nilai($huruf)
	{
		if ( ! $huruf) return '<span class="badge badge-light">Belum dinilai</span>';
		$warna = in_array($huruf, ['A', 'AB'], TRUE) ? 'success' : (in_array($huruf, ['B', 'BC', 'C'], TRUE) ? 'primary' : 'danger');
		return '<span class="badge badge-'.$warna.'">'.$huruf.'</span>';
	}
}
