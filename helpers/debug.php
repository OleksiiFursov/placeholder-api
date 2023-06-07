<?php
if(isset($_GET['undebug'])){
    setcookie('debug', 1, time(), '/', URL_DOMAIN);
}

if(defined('IS_DEV')){
    setcookie('debug', 1, time()+3600, '/', URL_DOMAIN);
    include DIR.'/test.php';
}



class Perfomance{
    public $times = [],
           $last = 0,
            $comment = [];

    function __construct()
    {
       $this->point('Start');
    }

    function save($file='1.php'){
        file_put_contents(DIR.'/logs/'.$file, $this->output(false));
    }
    function point($comment=null){
        $this->last =  microtime (true);
        $this->times[] = $this->last;
        $this->comment[] = $comment?$comment: 'Point #'.sizeof($this->times);
    }

    function output($show=true){
        $str = '';
        foreach($this->times as $i=>$item){
           // $prev = isset($this->times[$i-1]) ?$this->times[$i-1] : 0;

            $str.='<p>'.$this->comment[$i].' - '.($item).'</p>'."\n";
        }
        if($show)
            echo $str;
        return $str;
    }
}
