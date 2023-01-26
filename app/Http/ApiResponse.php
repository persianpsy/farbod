<?php
/**
 * Written by farbod
 */

namespace App\Http;

/**
 * Class ApiResponse
 *
 *
 */
class ApiResponse
{

    private $fields = [
        'status'      => '403',
        'action'      => null,
        'tag'         => null,
        'message'     => null,
        'data'        => [],
        'api_version' => '1.0.0',
    ];

//    public function __call($name, $arguments)
//    {
//        if(!array_key_exists($name, $this->fields)) {
//            throw new \Exception("$name is not modified in fields array, so you can\'t call it as a function");
//        }
//
//        if(array_key_exists($name, $this->fields)) {
//            $this->fields[$name] = array_first($arguments);
//        }
//
//        return $this;
//    }
//
//    public function __get($var)
//    {
//        return $this->fields[$var] ?? null;
//    }
//
//    public function __set($var, $val)
//    {
//        if(!array_key_exists($var, $this->fields)) {
//            throw new \Exception("$var is not modified in fields array, so you can\'t set it as a object variable");
//        }
//
//        $this->fields[$var] = $val;
//    }

//    public function get()
//    {
//        return $this->fields;
//    }

    public function response($status = '200',  $headers = [])
    {
        $this->corrections();

        return response()->json($this->fields, $status, $headers);
    }

    /**
     * Modificate data
     *
     * @return void
     */
    protected function corrections()
    {
        if(str_contains($this->fields['message'], 'tr:')) {
            $this->fields['message'] = trans(str_after($this->fields['message'], 'tr:'));
        }

        if($this->fields['data'] instanceof TransformableContract) {
            $this->fields['data'] = $this->fields['data']->transform();
        }

        if($this->fields['action'] === null) {
            $this->fields['action'] = $this->defaultActionBuilder();
        }
    }

    protected function defaultActionBuilder()
    {
        $segments = request()->segments();
        // Remove 'api' prefix
        if(isset($segments[0]) && $segments[0] == 'api') {
            unset($segments[0]);
        }
        // Get parameters and replace values with keys
        $parameters = request()->route()->parameters;
        foreach($segments as $key => $value) {
            $parameter_key  = array_search($value, $parameters, true);
            $segments[$key] = $parameter_key
                ? studly_case($parameter_key)
                : studly_case($value)
            ;
        }

        // Convert to string
        $action = collect($segments)->implode('');

        return $action;
    }
}
