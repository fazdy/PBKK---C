<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper pembuat elemen form Bootstrap 4 lengkap dengan pesan validasi CodeIgniter.
 * $default adalah nilai lama (saat edit); set_value() memakai nilai POST bila form gagal validasi.
 */

if ( ! function_exists('bs_input'))
{
	function bs_input($name, $label, $default = '', $type = 'text', $extra = '', $help = '')
	{
		$error = form_error($name, ' ', ' ');
		return '<div class="form-group">'
			.'<label for="'.$name.'">'.$label.'</label>'
			.'<input type="'.$type.'" class="form-control'.($error ? ' is-invalid' : '').'" id="'.$name.'" name="'.$name.'" value="'.($type === 'password' ? '' : html_escape(set_value($name, $default))).'" '.$extra.'>'
			.($error ? '<div class="invalid-feedback">'.$error.'</div>' : '')
			.($help ? '<small class="form-text text-muted">'.$help.'</small>' : '')
			.'</div>';
	}
}

if ( ! function_exists('bs_select'))
{
	function bs_select($name, $label, $options, $default = '')
	{
		$error = form_error($name, ' ', ' ');
		return '<div class="form-group">'
			.'<label for="'.$name.'">'.$label.'</label>'
			.form_dropdown($name, $options, set_value($name, $default), 'class="form-control'.($error ? ' is-invalid' : '').'" id="'.$name.'"')
			.($error ? '<div class="invalid-feedback">'.$error.'</div>' : '')
			.'</div>';
	}
}
