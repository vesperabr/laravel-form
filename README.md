This Laravel package provides some Blade directives to made form creation a snap.

## Installation

You can install this package via composer using:

```bash
$ composer require vesperabr/laravel-form
```

The package will automatically register its service provider.

## Usage

After install, you can use any of bellow directives in your blade files.

```blade
@f_open()
  @f_text('name', 'Nome', true)
  @f_email('email, 'E-mail')
@f_close
```

## Blade directives avaiable

### Opening a form
- f_open($action, $method, $model, $attributes)
- f_close()
- f_hidden($name, $attributes)
- f_text($name, $label, $required, $attributes)
- f_tel($name, $label, $required, $attributes)
- f_email($name, $label, $required, $attributes)
- f_url($name, $label, $required, $attributes)
- f_search($name, $label, $required, $attributes)
- f_password($name, $label, $required, $attributes)
- f_number($name, $label, $required, $attributes)
- f_cpfcnpj($name, $label, $required, $attributes)
- f_cpf($name, $label, $required, $attributes)
- f_cnpj($name, $label, $required, $attributes)
- f_cep($name, $label, $required, $attributes)
- f_money($name, $label, $required, $attributes)
- f_float($name, $label, $required, $attributes)
- f_date($name, $label, $required, $attributes)
- f_time($name, $label, $required, $attributes)
- f_file($name, $label, $required, $attributes)
- f_textarea($name, $label, $required, $attributes)
- f_select($name, $label, $required, $items, $attributes)
- f_select2($name, $label, $required, $items, $attributes)
- f_multiselect($name, $label, $required, $items, $attributes)
- f_multiselect2($name, $label, $required, $items, $attributes)
- f_checkbox($name, $label, $items, $checked)
- f_radio($name, $label, $items, $checked)
- f_buttons($submit_label, $cancel_link)


## Requirements

- PHP 7.2
- Laravel 6
- Monalisa CSS & JS

This package wasn't been tested yet in older Laravel versions. Feel free to test and send us your experience.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
