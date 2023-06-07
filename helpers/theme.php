<?php






function get_header($args=[]){
    include load_view('part/header');
}
function get_content(){
    $cont = ini_r('view.content');
    $a = &$cont;
    event('view.content', $a);
}
function get_footer($args=[]){
    include load_view('part/footer');
}
function get_view_part($file, $args=[]){
    include load_view($file);
}

function theme_title($echo=true){
    $title = get_option('site.name');
    $sub_title = ini('page.title');

    if(!$sub_title){
        $sub_title = implode(get_option('site.separator'), def(ini('breadcrumbs.link'), []));
    }

    $title .= ' '.$sub_title;
    if($echo){
        echo $title;
    }else{
        return $title;
    }
}

function theme_head(){
    get_css('header');
    get_js('header');
}
function theme_foot(){
    get_css();
    get_js();
}



$_JS = array();
$_CSS = array();



function set_css($name, $url=null, $place='footer'){
    global $_CSS;

    if(is_array($name)){
        foreach($name as $_name=>$_url){ // url => $place
            set_css($_name, $_url, $url?$url:'footer');
        }
        return;
    }

    if(!isset($_CSS[$place])){
        $_CSS[$place] = array();
    }
    $_CSS[$place][$name] = $url;
}

function get_css($place='footer'){
    global $_CSS, $conf;
    $buf = '';
    if(isset($_CSS[$place])) {

        foreach ($_CSS[$place] as $name => $url) {
            if ($conf['isDev']) {
                $url .= '?time=' . time();
            }
            $buf .= '<link rel="stylesheet" href="' . $url . '" />';
        }
    }
    echo $buf;
}

function set_js($name, $url=null, $place='footer'){
    global $_JS;

    if(is_array($name)){
        foreach($name as $_name=>$_url){ // url => $place
            set_js($_name, $_url, $url?$url:'footer');
        }
        return;
    }

    if(!isset($_JS[$place])){
        $_JS[$place] = array();
    }
    $_JS[$place][$name] = $url;
}

function get_js($place='footer'){
    global $_JS, $conf;

    $buf = '';
    if(isset($_JS[$place])) {
        foreach ($_JS[$place] as $name => $url) {
            if ($conf['isDev']) {
                $url .= '?time=' . time();
            }
            $buf .= '<script defer src="' . $url . '"></script>';
        }
    }
    echo $buf;
}


ini_set('display_errors', true);

function get_pages($q, &$opt = []){

    $opt+=[
        'key_separator' => '_',
        'dir_separator' => '/',
        'array'         => [],
        'prefix_key'    => ''
    ];

    $files = scandir($q);

    for($i=2, $len=sizeof($files); $i<$len; $i++) {
        $arr_key  = $opt['prefix_key'] . ($opt['prefix_key']?$opt['key_separator']:'').$files[$i];

        if(!is_file($q.'/'.$files[$i])){
            $opt['prefix_key'] = $arr_key;

            get_pages($q.$opt['dir_separator'].$files[$i], $opt);
        }else{

            $opt['array'][str_replace('.tpl', '', $arr_key)] = $q.'/'. str_replace('.tpl', '', $files[$i]);
        }
    }

    $opt['prefix_key'] = explode('/',$opt['prefix_key']);
    array_pop($opt['prefix_key']);
    $opt['prefix_key'] = implode('/',  $opt['prefix_key']);

    return $opt['array'];
}
ini('document.type', 'html');



include DIR_VIEW.'/init.php';
