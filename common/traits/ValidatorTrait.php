<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/4/28
 * Time: 11:32
 */

namespace common\traits;

trait ValidatorTrait {

    /**
     *
     * @param $rule
     * @return \Closure|mixed
     */
    private function builtInValidators($rule) {
        $funcs = [
            'required' => function(&$data,&$key,$args = []) {
                return isset($data[$key]) && !empty($data[$key]);
            },
            'email' => function() {},
            'numeric' => function(&$data,&$key,$args = []) {
                return isset($data[$key]) && is_integer($data[$key]);
            },
            'phone' => function(&$data,&$key,$args = []) {},
            'url' => function(&$data,&$key,$args = []) {
                
            },
            'array' => function(&$data,&$key,$args = []) {
                return is_array($data[$key]);
            },
            'date' => function(&$data,&$key,$args = []) {
                return date_parse($data[$key]);
            },
            'ip' => function(&$data,&$key,$args = []) {},
            'json' => function(&$data,&$key,$args = []) {
                return json_decode($data[$key]) !== null;
            },
            'max' => function(&$data,&$key,$args) {
                return $data[$key] <= $args[0];
            },
            'min' => function(&$data,&$key,$args) {
                return $data[$key] >= $args[0];
            },
            'string' => function(&$data,&$key,$args = []) {
                return is_string($data[$key]);
            },
            'in' => function(&$data,&$key,$args) {
                return in_array($data[$key],$args);
            }
        ];
        $default = function(&$data,&$key,$args = []) {return true;};
        return isset($funcs[$rule]) ? $funcs[$rule] : $default;
    }

    /**
     *
     * @param $field
     * @param $rule
     * @return mixed
     */
    private function defaultValidatorMessages() {
        return function($field,$rule)  {
            $messages = [
                'required' => "%s is required.",
                'string' => "%s is must be a string.",
                'numeric' => "%s is must be a number."
            ];
            if(!isset($messages[$rule])) return sprintf("error message for `%s`.`%s` not defined",$field,$rule);

            return sprintf($messages[$rule],$field);
        };
    }

    /**
     * @param $rules
     * @return array
     */
    private function parseRules($rules) {
        $rules = is_array($rules) ? $rules : explode("|",$rules);
        $parse = [];
        array_walk($rules,function(&$rule) use(&$parse){
            $matches = preg_split("#[\|:|,]#",$rule);
            $parse[] = [
                'rule' => array_shift($matches),
                'args' => $matches
            ];
        });
        return $parse;
    }

    /**
     * eg.
     * $data = ['id' => 1];
     * $rules = ['id' => 'required|max:10|min:1|in:1,2,3,4,5,6,7,8,10|email','tel' => ['required','phone']];
     * $messages = ['id.required' => 'id is required','id.string' => 'id must be a string'];
     * @param $data
     * @param $rules
     * @param $messages
     * @return \stdClass
     */
    public function validator($data,$rules,$messages = []) {
        $error = new \stdClass();
        $errors_info = [];

        function recordError($field,$rule,&$messages,$default_message) {
            return [
                'filed' => $field,
                'rule' => $rule,
                'error' => isset($messages[$field.'.'.$rule]) ? $messages[$field.'.'.$rule] : $default_message($field,$rule)
            ];
        }
        foreach($rules as $field => $rule) {
            $parse_rules = $this->parseRules($rule);
            //var_dump($parse_rules);
            foreach($parse_rules as $_rule) {
                $validator = $this->builtInValidators($_rule['rule']);
                if($validator($data,$field,$_rule['args']) === false) {
                    $errors_info[] = recordError($field,$_rule['rule'],$messages,$this->defaultValidatorMessages());
                }
            }
        }
        $error->success = empty($errors_info);
        $error->errors = $errors_info;
        $error->first = function() use($errors_info) {
            return isset($errors_info[0]) ? $errors_info[0] : '';
        };
        return $error;
    }
}