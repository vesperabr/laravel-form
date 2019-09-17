<?php

namespace Vespera\LaravelForm;

use Vespera\LaravelForm\FormBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{

    protected $directives = ['f_open', 'f_close', 'f_hidden', 'f_input', 'f_text', 'f_tel', 'f_email', 'f_url', 'f_search', 'f_password', 'f_number', 'f_cpfcnpj', 'f_cpf', 'f_cnpj', 'f_cep', 'f_money', 'f_float', 'f_date', 'f_time', 'f_file', 'f_textarea', 'f_select', 'f_multiselect', 'f_select2', 'f_multiselect2', 'f_checkbox', 'f_radio', 'f_buttons'];

    protected $defer = true;

    public function register()
    {
        $this->app->singleton('form', function($app) {
            return new FormBuilder($app['request'], $app['view'], $app['session.store']->token());
        });
    }

    public function boot()
    {
        foreach ($this->directives as $directive) {
            Blade::directive($directive, function($expression) use ($directive) {
                return "<?php echo Form::$directive($expression); ?>";
            });
        }
    }

}
