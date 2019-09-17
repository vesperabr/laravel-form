<?php

namespace Vespera\LaravelForm;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Collection;
use Illuminate\View\Factory;

class FormBuilder
{
    protected $request;
    protected $errors;
    protected $model;

    protected $csrfToken;
    protected $spoofedMethods = ['DELETE', 'PATCH', 'PUT'];
    protected $skipValueTypes = ['file', 'password', 'checkbox', 'radio'];

    /**
     * Create a new form builder instance.
     * @param Request $request
     * @param Factory $view
     * @param string  $csrfToken
     */
    public function __construct(Request $request, Factory $view, string $csrfToken)
    {
        $this->request   = $request;
        $this->errors    = $view->shared('errors');
        $this->csrfToken = $csrfToken;
    }

    /**
     * Open up a new HTML form.
     * @param  string $action
     * @param  string $method
     * @param  mixed  $model
     * @param  array  $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public function f_open(string $action = '', string $method = 'POST', $model = null, array $attributes = [])
    {
        // Set the default values
        $default = [
            'action' => $action,
            'method' => $this->_getMethod($method),
            'class' => ['Form'],
        ];

        // Prepare class attributes then merge default and user attributes
        $attributes = $this->_mergeAttributesArray($default, $attributes);

        // Set model property as a \Illuminate\Support\Collection
        if ($model) {
            $this->model = collect($model);
        }

        // Get things to be appended to the form
        $append = $this->_getAppendage($method);

        $html  = "<form ". $this->_arrayToHtmlAttributes($attributes) .">\n";
        $html .= $append;

        return $this->_toHtmlString($html);
    }

    /**
     * Close the current form.
     * @return \Illuminate\Support\HtmlString
     */
    public function f_close()
    {
        $this->model = null;
        return $this->_toHtmlString('</form>');
    }

    /**
     * Create a form input field.
     * @param  string  $type
     * @param  string  $name
     * @param  string  $label
     * @param  boolean $required
     * @param  array   $attributes
     * @return \Illuminate\Support\HtmlString
     */
    public function f_input(string $type = 'text', string $name = '', string $label = '', bool $required = false, array $attributes = [])
    {
        $default = [
            'type'     => $type,
            'name'     => $name,
            'id'       => isset($attributes['id']) ? $attributes['id'] : $name,
            'required' => $required,
        ];

        // Check for errors
        if ($this->errors->has($name)) {
            $default['class'][] = '_error';
        }

        // Define input value
        if (! in_array($type, $this->skipValueTypes)) {
            $attributes['value'] = $this->_getValueAttribute($name, $attributes);
        }

        // Merge attributes
        $attributes = $this->_mergeAttributesArray($default, $attributes);

        // Label
        $for = isset($attributes['id']) ? $attributes['id'] : $name;
        $label = $this->f_label($for, $label, $required);

        // Input
        $input = "<input ". $this->_arrayToHtmlAttributes($attributes) .">";

        return $this->_template($label, $input);
    }

    /**
     * Create a hidden input field.
     * @param  string $name
     * @param  array  $attributes
     * @return string
     */
    public function f_hidden(string $name, array $attributes = [])
    {
        $default = [
            'type'  => 'hidden',
            'name'  => $name,
        ];

        $attributes = $this->_mergeAttributesArray($default, $attributes);

        $html = "<input ". $this->_arrayToHtmlAttributes($attributes) .">";
        return $this->_toHtmlString($html);
    }

    public function f_text(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_tel(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $data['data-mask-name'] = 'tel';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_email(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('email', $name, $label, $required, $attributes);
    }

    public function f_url(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['placeholder'] = isset($attributes['placeholder']) ? $attributes['placeholder'] : 'http://';
        return $this->f_input('url', $name, $label, $required, $attributes);
    }

    public function f_search(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('search', $name, $label, $required, $attributes);
    }

    public function f_password(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('password', $name, $label, $required, $attributes);
    }

    public function f_number(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('number', $name, $label, $required, $attributes);
    }

    public function f_cpfcnpj(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'cpfcnpj';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_cpf(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'cpf';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_cnpj(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'cnpj';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_cep(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'cep';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_money(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'money';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_float(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $attributes['data-mask-name'] = 'float';
        return $this->f_input('text', $name, $label, $required, $attributes);
    }

    public function f_date(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('date', $name, $label, $required, $attributes);
    }

    public function f_time(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('time', $name, $label, $required, $attributes);
    }

    public function f_file(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        return $this->f_input('file', $name, $label, $required, $attributes);
    }

    public function f_textarea(string $name, string $label = '', bool $required = false, array $attributes = [])
    {
        $default = [
            'name'     => $name,
            'id'       => isset($attributes['id']) ? $attributes['id'] : $name,
            'required' => $required,
        ];

        // Check for errors
        if ($this->errors->has($name)) {
            $default['class'][] = '_error';
        }

        // Merge attributes
        $attributes = $this->_mergeAttributesArray($default, $attributes);

        // Extract value from attributes array
        $value = $this->_getValueAttribute($name, $attributes);
        unset($attributes['value']);

        // Label
        $for = isset($attributes['id']) ? $attributes['id'] : $name;
        $label = $this->f_label($for, $label, $required);

        // Textarea
        $textarea = "<textarea ". $this->_arrayToHtmlAttributes($attributes) .">$value</textarea>";

        return $this->_template($label, $textarea);
    }

    public function f_select(string $name, string $label = '', bool $required = false, $items = [], array $attributes = [])
    {
        $default = [
            'name'     => $name,
            'id'       => isset($attributes['id']) ? $attributes['id'] : $name,
            'required' => $required,
        ];

        // Check for errors
        if ($this->errors->has($name)) {
            $default['class'][] = '_error';
        }

        // Merge attributes
        $attributes = $this->_mergeAttributesArray($default, $attributes);

        // Label
        $for = isset($attributes['id']) ? $attributes['id'] : $name;
        $label = $this->f_label($for, $label, $required);

        // Items
        $items = collect($items);
        $items->prepend('Selecione...', '');

        // Selected
        $selected = $this->_getValueAttribute($name, $attributes);
        $options = '';

        foreach ($items as $key => $value) {
            if ($key == $selected) {
                $options .= "<option value='$key' selected>$value</option>";
            } else {
                $options .= "<option value='$key'>$value</option>";
            }
        }

        unset($attributes['value']);

        $select = "<select ". $this->_arrayToHtmlAttributes($attributes) .">". $options ."</select>";
        return $this->_template($label, $select);
    }

    public function f_select2(string $name, string $label = '', bool $required = false, array $items = [], array $attributes = [])
    {
        $classes = isset($attributes['class']) ? $attributes['class'] : [];
        $attributes['class'] = collect($classes)->prepend('select2')->toArray();

        return $this->f_select($name , $label, $required, $items, $attributes);
    }

    public function f_multiselect(string $name, string $label = '', bool $required = false, array $items = [], array $attributes = [])
    {
        $default = [
            'name'     => $name,
            'id'       => isset($attributes['id']) ? $attributes['id'] : $name,
            'required' => $required,
            'multiple' => true
        ];

        $name_without_brackets = str_replace('[]', '', $name);

        // Check for errors
        if ($this->errors->has($name_without_brackets)) {
            $default['class'][] = '_error';
        }

        // Merge attributes
        $attributes = $this->_mergeAttributesArray($default, $attributes);

        // Label
        $for = isset($attributes['id']) ? $attributes['id'] : $name;
        $label = $this->f_label($for, $label, $required);

        // Items
        $items = collect($items);

        // Selected
        $selected = $this->_getValuesAttribute($name_without_brackets, $attributes);
        $options = '';

        foreach ($items as $key => $value) {
            if (in_array($key, $selected)) {
                $options .= "<option value='$key' selected>$value</option>";
            } else {
                $options .= "<option value='$key'>$value</option>";
            }
        }

        unset($attributes['value']);

        $select = "<select ". $this->_arrayToHtmlAttributes($attributes) .">". $options ."</select>";
        return $this->_template($label, $select);
    }

    public function f_multiselect2(string $name, string $label = '', bool $required = false, array $items = [], array $attributes = [])
    {
        $classes = isset($attributes['class']) ? $attributes['class'] : [];
        $attributes['class'] = collect($classes)->prepend('select2')->toArray();

        return $this->f_multiselect($name , $label, $required, $items, $attributes);
    }

    public function f_checkbox(string $name, string $label = '', array $items, array $checked = [])
    {
        $label = $this->f_label($name, $label, false);
        $fields = '';

        $name_without_brackets = str_replace('[]', '', $name);
        $items = collect($items);

        if (count($checked)) {
            $checked = $this->_getValuesAttribute($name_without_brackets, ['value' => $checked]);
        } else {
            $checked = $this->_getValuesAttribute($name_without_brackets, []);
        }

        foreach ($items as $key => $value) {
            $c = (in_array($key, $checked)) ? 'checked' : '';
            $fields .= "<label><input type='checkbox' name='$name' value='$key' $c> $value</label>";
        }

        return $this->_template($label, $fields);
    }

    public function f_radio(string $name, string $label = '', array $items, string $checked = '')
    {
        $label = $this->f_label($name, $label, false);
        $fields = '';

        $items = collect($items);

        if (! empty($checked)) {
            $checked = $this->_getValueAttribute($name, ['value' => $checked]);
        } else {
            $checked = $this->_getValueAttribute($name, []);
        }

        foreach ($items as $key => $value) {
            $c = ($key == $checked) ? 'checked' : '';
            $fields .= "<label><input type='radio' name='$name' value='$key' $c> $value</label>";
        }

        return $this->_template($label, $fields);
    }

    public function f_buttons(string $submit_label = 'Salvar', string $cancel_link = '')
    {
        $html = "<div class='form-buttons'><div>";
        $html .= "<button class='Button _primary' type='submit'>$submit_label</button>";

        if (!empty($cancel_link)) {
            $html .= "<a href='$cancel_link' class='Button _secondary _outline'>Cancelar</a>";
        }

        $html .= "</div></div>";

        return $this->_toHtmlString($html);
    }




    /**
     * Create a form label element.
     * @param  string  $for
     * @param  string  $label
     * @param  boolean $required
     * @return string
     */
    protected function f_label(string $for, string $label, bool $required = false)
    {
        if (! $label) {
            return '';
        }

        if ($label && $required) {
            $label .= '*';
        }

        $attributes['for'] = $for;

        // Error
        if ($this->errors->has($for)) {
            $attributes['class'][] = '_error';
        }

        return "<label ". $this->_arrayToHtmlAttributes($attributes) .">$label</label>";
    }

    /**
     * Get the form appendage for the given method.
     * @param  string $method
     * @return string
     */
    protected function _getAppendage(string $method)
    {
        $method = strtoupper($method);
        $appendage = '';

        // If the HTTP method is in this list of spoofed methods, we will attach the
        // method spoofer hidden input to the form. This allows us to use regular
        // form to initiate PUT and DELETE requests in addition to the typical.
        if (in_array($method, $this->spoofedMethods)) {
            $appendage .= $this->f_hidden('_method', ['value' => $method]) . "\n";
        }

        // If the method is something other than GET we will go ahead and attach the
        // CSRF token to the form, as this can't hurt and is convenient to simply
        // always have available on every form the developers creates for them.
        if ($method !== 'GET') {
            $appendage .= $this->f_hidden('_token', ['value' => $this->csrfToken]) . "\n";
        }

        return $appendage;
    }

    /**
     * Parse the form action method.
     * @param  string $method
     * @return string
     */
    protected function _getMethod(string $method)
    {
        $method = strtoupper($method);
        return $method !== 'GET' ? 'POST' : $method;
    }

    /**
     * Get the value that should be assigned to the field.
     * @param  string $name
     * @param  array  $attributes
     * @return string
     */
    protected function _getValueAttribute(string $name, array $attributes)
    {
        // 1. Does we have a value in the old flashdata?
        $old = $this->request->old($name);
        if (! is_null($old) && $name !== '_method') {
            return $old;
        }

        // 2. Does we have a value in the attributes param?
        if (isset($attributes['value'])) {
            return $attributes['value'];
        }

        // 3. Does we have a value in the model?
        if ($this->model && $this->model->get($name)) {
            return $this->model->get($name);
        }

        // 4. We doesn't have any value, return null
        return '';
    }

    /**
     * Get the values that should be assigned to the multi select field.
     * @param  string $name
     * @param  array  $attributes
     * @return array
     */
    protected function _getValuesAttribute(string $name, array $attributes)
    {

        // 1. Does we have a value in the old flashdata?
        $old = $this->request->old($name);
        if (! is_null($old) && $name !== '_method') {
            return $old;
        }

        // 2. Does we have a value in the attributes param?
        if (isset($attributes['value'])) {
            return collect($attributes['value'])->toArray();
        }

        // 3. Does we have a value in the model?
        if ($this->model && $value = $this->model->get($name . '_dropdown')) {
            $value = collect($value)->toArray();
            return $value;
        }

        // 4. We doesn't have any value, return null
        return [];
    }

    /**
     * Prepare class attribute to be always a array
     * @param  array  $attributes
     * @return array
     */
    protected function _prepareClassAttribute(array $attributes)
    {
        if (isset($attributes['class'])) {
            if (! is_array($attributes['class'])) {
                $attributes['class'] = [$attributes['class']];
            }
        } else {
            $attributes['class'] = [];
        }

        return $attributes;
    }

    /**
     * Merge default attributes with custom attributes.
     * @param  array  $default
     * @param  array  $attributes
     * @return array
     */
    protected function _mergeAttributesArray(array $default, array $attributes)
    {
        $default = $this->_prepareClassAttribute($default);
        $attributes = $this->_prepareClassAttribute($attributes);

        $default['class'] = array_merge($default['class'], $attributes['class']);
        $attributes = array_merge($attributes, $default);

        return $attributes;
    }

    /**
     * Convert attributes array to a string with HTML attributes.
     * @param  array  $attributes
     * @return string
     */
    protected function _arrayToHtmlAttributes(array $attributes)
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            $element = null;

            if (is_bool($value) && $key !== 'value') {
                $element = $value ? $key : null;
            }

            if (is_array($value) && $key === 'class') {
                $element = 'class="' . implode(' ', $value) . '"';
            }

            if (! is_null($value) && ! is_bool($value) && ! is_array($value)) {
                $element = $key . '="' . e($value, false) . '"';
            }

            if (! is_null($element)) {
                $html[] = $element;
            }

            // Reset $element
            $element = null;
        }

        return count($html) > 0 ? rtrim(implode(' ', $html)) : '';
    }

    /**
     * Transform a string to an HTML object.
     * @param  string $html
     * @return \Illuminate\Support\HtmlString
     */
    protected function _toHtmlString(string $html)
    {
        return new HtmlString($html);
    }

    /**
     * Parse the form item template.
     * @param  string $label
     * @param  string $field
     * @return \Illuminate\Support\HtmlString
     */
    protected function _template(string $label, string $field)
    {
        $html = "<div class='form-item'>$label<div>$field</div></div>";
        return $this->_toHtmlString($html);
    }
}
