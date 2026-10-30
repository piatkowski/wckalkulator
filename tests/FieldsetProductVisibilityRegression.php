<?php
/**
 * Dependency-free visibility regression checks.
 * Run with: php -n tests/FieldsetProductVisibilityRegression.php
 */
namespace {
    error_reporting(0);
    function __($value) { return $value; }
    require __DIR__ . '/../src/FieldsetProduct.php';

    class FakeField {
        private $values;
        public function __construct($name, $visibility = '', $required = false, $whenVisible = false, $type = 'text', $group = 'input') {
            $this->values = array('name'=>$name, 'title'=>$name, 'visibility'=>$visibility);
            $this->required=$required; $this->whenVisible=$whenVisible; $this->fieldType=$type; $this->fieldGroup=$group;
        }
        public $required; public $whenVisible; public $fieldType; public $fieldGroup;
        public function data($key='') { return $key === '' ? $this->values : (array_key_exists($key,$this->values) ? $this->values[$key] : null); }
        public function group() { return $this->fieldGroup; }
        public function type() { return $this->fieldType; }
        public function is_required() { return $this->required || $this->whenVisible; }
        public function is_required_when_visible() { return $this->whenVisible; }
    }
    $r = new ReflectionClass('WCKalkulator\\FieldsetProduct');
    $apply = $r->getMethod('apply_visibility_to_user_input'); $apply->setAccessible(true);
    $set = function($fieldset, $property, $value) use ($r) { $p=$r->getProperty($property); $p->setAccessible(true); $p->setValue($fieldset,$value); };
    $get = function($fieldset, $property) use ($r) { $p=$r->getProperty($property); $p->setAccessible(true); return $p->getValue($fieldset); };
    $new = function($fields,$input) use ($r,$set) { $f=$r->newInstanceWithoutConstructor(); $set($f,'fields',$fields); $set($f,'user_input',$input); return $f; };
    $passed=0; $failed=array();
    $check=function($name,$ok) use (&$passed,&$failed) { if($ok){$passed++;}else{$failed[]=$name;} };
    $condition=function($field,$value,$comp='3') { return array('field'=>$field,'comp'=>$comp,'value'=>$value); };
    $rulesAnd=array(array($condition('A','yes'),$condition('B','yes')));
    $fields=array('A'=>new FakeField('A'),'B'=>new FakeField('B'),'C'=>new FakeField('C',json_encode($rulesAnd),false,true));
    $f=$new($fields,array('A'=>'yes','B'=>'yes')); $error=null; $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('AND true/true shows field',$state['C']===true);
    $f=$new($fields,array('A'=>'yes','B'=>'no')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('AND true/false hides field',$state['C']===false);
    $f=$new($fields,array('A'=>'no','B'=>'yes')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('AND false/true hides field',$state['C']===false);
    $f=$new($fields,array('A'=>'no','B'=>'no')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('AND false/false hides field',$state['C']===false);

    $orRules=array(array($condition('A','yes')),array($condition('B','yes')));
    $orFields=array('A'=>new FakeField('A'),'B'=>new FakeField('B'),'C'=>new FakeField('C',json_encode($orRules),false,true));
    $f=$new($orFields,array('A'=>'yes','B'=>'no')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('OR true/false shows field',$state['C']===true);
    $f=$new($orFields,array('A'=>'no','B'=>'yes')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('OR false/true shows field',$state['C']===true);
    $f=$new($orFields,array('A'=>'yes','B'=>'yes')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('OR true/true shows field',$state['C']===true);
    $f=$new($orFields,array('A'=>'no','B'=>'no')); $state=$f->evaluate_visibility($get($f,'user_input'),$error); $check('OR false/false hides field',$state['C']===false);

    $missing=array('C'=>new FakeField('C',json_encode(array(array($condition('MISSING','x')))),false,true));
    $f=$new($missing,array()); $error=null; $check('missing reference rejected',$f->evaluate_visibility(array(),$error)===false && $error==='visibility_reference_missing');
    $cycle=array('A'=>new FakeField('A',json_encode(array(array($condition('B','x'))))),'B'=>new FakeField('B',json_encode(array(array($condition('A','x'))))));
    $f=$new($cycle,array('A'=>'x','B'=>'x')); $error=null; $check('cycle rejected',$f->evaluate_visibility(array('A'=>'x','B'=>'x'),$error)===false && $error==='visibility_cycle');

    $requiredFields=array('A'=>new FakeField('A'),'C'=>new FakeField('C',json_encode(array(array($condition('A','yes')))),false,true));
    $f=$new($requiredFields,array('A'=>'no','C'=>'client supplied secret')); $check('hidden value is neutralized',$apply->invoke($f)===true && $get($f,'user_input')['C']==='');
    $check('hidden required field may be absent',$f->validate_names()===true);
    $f=$new($requiredFields,array('A'=>'yes')); $apply->invoke($f); $check('visible required field omission rejected',$f->validate_names()===false);
    $f=$new($requiredFields,array('A'=>'yes','C'=>'0')); $apply->invoke($f); $check('zero string is present',$f->validate_names()===true);

    $emptyRules=array('A'=>new FakeField('A'),'C'=>new FakeField('C',json_encode(array(array($condition('A','','1')))),false,true));
    $f=$new($emptyRules,array('A'=>'')); $state=$f->evaluate_visibility(array('A'=>''),$error); $check('empty comparison matches empty value',$state['C']===true);
    $f=$new($emptyRules,array('A'=>'0')); $state=$f->evaluate_visibility(array('A'=>'0'),$error); $check('zero string is not empty',$state['C']===false);

    echo 'Fieldset visibility regression: '.$passed.'/'.($passed+count($failed)).' passed'.PHP_EOL;
    if($failed){echo implode(PHP_EOL,$failed).PHP_EOL;exit(1);}
}
