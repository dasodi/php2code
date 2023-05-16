<?php
/*
Aplicacion:     php2code - utils
Autor:          Dario Soto Diaz
Version:        1.0
Descripcion:    pasa el codigo de una aplicacion web a un archivo texto para su registro
Fecha Mod.:     10-05-2023
*/

//establece el uso horario
date_default_timezone_set('Europe/Madrid');

//inicializa valores
$b_imprimir=true;
$m_error='';
$informe='';
define('CONF_MODO_LOCAL',true);

//================================== configuracion ============================================

//1. nombre script
$script = explode(DIRECTORY_SEPARATOR,__FILE__);
$script_name = $script[count($script)-1];

//2. carpeta del script
$dir_script = dirname(__FILE__);

//3. obtiene parametros des archivo ini
$ini_file = $dir_script . DIRECTORY_SEPARATOR . 'ini.php';
if(file_exists($ini_file)){
    $conf = parse_ini_file($ini_file, true);
    if(!is_array($conf)){
        die("El archivo $ini_file no es valido");
    }
}else{
    die("No existe archivo $ini_file");
}

//4. path absoluto de la carpeta de la aplicacion a guardar su codigo fuente
$dir_app = $conf['App']['dir_app'];
if(!file_exists($dir_app)){
    echo 'No existe la carpeta de aplicacion: '. $dir_app;
    exit();
}

//5. path absoluto de la carpeta en la que se guarda el archivo del codigo fuente
$dir_code = $conf['App']['dir_code'];
if(!file_exists($dir_code)){
    echo 'No existe la carpeta: '. $dir_code;
    exit();
}

//6. extensiones a agregar al codigo fuente
$extensions = $conf['App']['extensions'];

//7. carpetas a excluir del codigo fuente
$no_dirs = $conf['App']['no_dirs'];

//8. archivos a excluir del codigo fuente
$no_files = $conf['App']['no_files'];


//================================== fin configuracion ========================================

//obtiene nombre de la aplicacion en archivo ini de la app a codificar
$app_file = $dir_app . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'conn.php';
if(file_exists($app_file)){
    $conn = parse_ini_file($app_file,true);
    if(!is_array($conn)){
        die("El archivo $app_file no es valido");
    }
}else{
    die("No existe archivo $app_file");
}
$app_name = $conn['App']['name'];
$app_author = $conn['App']['autor'];
$app_version = $conn['App']['version'];

//-------------- define variables globales -------------------------

//carpetas a excluir
$global_no_dirs = array();
$global_no_dirs = explode(',',$no_dirs);

//archivos a excluir
$global_no_files = array();
$global_no_files = explode(',',$no_files);

//hash global md5 de los archivos agregados al codigo fuente
$global_md5 = '';

//guarda la relacion de los archivos agregados al codigo fuente
$global_arr = array();
$global_arr[] = 'file,'."\t".'datetime,'."\t".'size,'."\t".'md5';

//crea nombre que va a tener el archivo de lcodigo fuente
$code_file = $dir_code . DIRECTORY_SEPARATOR. strtolower($app_name) .'-code_' . date('YmdHi') . '.txt';
if(file_exists($code_file)){
    @ unlink($code_file);
}

//agrega cabecera al archivo de codigo fuente
$fp = fopen($code_file, 'a');
fwrite($fp, "========================================================================================" . "\n");
fwrite($fp, 'Nombre Aplicacion:' . "\t" . "\t" . $app_name . "\n");
fwrite($fp, 'Nombre Autor:' . "\t" . "\t" . "\t" . $app_author . "\n");
fwrite($fp, 'Version:' . "\t" . "\t" . "\t" . "\t" . $app_version . "\n");
fwrite($fp, 'Carpeta Aplicacion:' . "\t" . "\t" . $dir_app . "\n");
fwrite($fp, 'Extensiones a codigo:' . "\t" . $extensions . "\n");
fwrite($fp, 'Carpetas Excluidas : ' . "\t" . implode(', ', $global_no_dirs) . "\n");
fwrite($fp, 'Archivos Excluidos : ' . "\t" . implode(', ', $global_no_files) . "\n");
fwrite($fp, 'Fecha Codigo:' . "\t" . "\t" . "\t" .date('d-m-Y') . "\n");
fwrite($fp, "========================================================================================" . "\n");
fclose($fp);

// recorre las carpetas y subcarpetas y crea archivo de codigo fuente de la aplicacion
get_folder_scripts($dir_app,$extensions);

//inicia informe
p_Imprimir($b_imprimir,'',$informe,2);
p_Imprimir($b_imprimir,'**************************************************************************************************',$informe);
p_Imprimir($b_imprimir,'Ejecutando script: '.$script_name.' --- Inicio script: '.date('Y-m-d H:i:s'),$informe);
p_Imprimir($b_imprimir,"====================================================================================",$informe);
p_Imprimir($b_imprimir,'App Nombre = '. $app_name,$informe);
p_Imprimir($b_imprimir,'App Version = '. $app_version,$informe);
p_Imprimir($b_imprimir,'App Autor = '. $app_author,$informe);
p_Imprimir($b_imprimir,'App Carpeta = '. $dir_app,$informe);
p_Imprimir($b_imprimir,'App Hash MD5 = '. $global_md5);
p_Imprimir($b_imprimir,'Archivo Codigo Fuente = '. $code_file,$informe);
p_Imprimir($b_imprimir,'Extensiones a Codigo = '. $extensions,$informe);
if(count($global_no_dirs) > 0){
    p_Imprimir($b_imprimir,'Carpetas Excluidas = '. implode(', ', $global_no_dirs));
}
if(count($global_no_files) > 0){
    p_Imprimir($b_imprimir,'Archivos Excluidos = '. implode(', ', $global_no_files));
}
p_Imprimir($b_imprimir,'====================================================================================',$informe);

//muestra archivos agregados al codigo fuente
$cont = 0;
foreach ( $global_arr as $row ) {
    if($cont == 0){
        $num = '#';
    }else{
        $num = $cont;
    }
    //para informe
    p_Imprimir($b_imprimir,$num . ',' . $row,$informe);

    $cont++;
}

p_Imprimir($b_imprimir,'====================================================================================',$informe);
p_Imprimir($b_imprimir, 'Fin script: '.date('Y-m-d H:i:s'),$informe);
p_Imprimir($b_imprimir,'**************************************************************************************************',$informe);


exit();

//------------------------ FUNCIONES DE LA PAGINA --------------------------------------------------

//obtiene configuracion de la app desde base de datos
function p_getConfig($host, $database, $user, $pass, &$error = ''){
    $conn = mysqli_connect($host, $user, $pass, $database);
    if(!$conn){
        echo "Error: No se pudo conectar a MySQL." . PHP_EOL . mysqli_connect_error();
        exit();
    }
    $sql = sprintf("SELECT * FROM sys_config ORDER BY IdConfiguracion DESC LIMIT 1");
    $query_id = mysqli_query($conn,$sql);
    if(!$query_id){
        $error = "Error query: " . PHP_EOL . mysqli_error($conn);
        return false;
    }
    $conf = mysqli_fetch_array($query_id);
    
    mysqli_close($conn);
    
    return $conf;
}

function p_Imprimir($imprimir,$txt,&$txt_msg='',$num_rc=0,$a_navegador=false){
    if(!$imprimir) return;

    if($a_navegador || CONF_MODO_LOCAL){
        if($num_rc > 0){
            for($i=0;$i<$num_rc;$i++){
                echo '<br>';
                $txt_msg.='<br>';
            }
        }else{
            echo $txt.'<br>';
            $txt_msg.=$txt.'<br>';
        }
    }else{
        if($num_rc > 0){
            for($i=0;$i<$num_rc;$i++){
                echo "\n";
                $txt_msg.="\n";
            }
        }else{
            echo $txt."\n";
            $txt_msg.=$txt."\n";
        }
    }
}

/**
 * Elimina el / al final de la ruta y asegurate de que sea una ruta absoluta
 *
 * @param unknown_type $dir
 * @return unknown
 *
 * Llamada recursiva para obtener md5
 *
 * @param string $dir1 Ruta 1, es estandar
 */
function get_folder_scripts($dir1,$extensions){
    global $global_md5;
    global $global_arr;
    global $code_file;
    global $cont;
    global $script_name;
    
    
    
    if (is_dir($dir1) && is_dir_in_array($dir1) === false) {
        $arr = scandir($dir1);
        foreach ($arr as $entry) {
            if (($entry != ".") && ($entry != "..") && ($entry != $script_name) && !is_file_in_array($entry)){
                $new = $dir1. DIRECTORY_SEPARATOR. $entry; // $new es el nombre completo del archivo o el nombre de la carpeta
                
                if(is_dir($new)) {
                    get_folder_scripts($new,$extensions) ;
                } else {
                    $ext = get_extension_file($new);
                    if(strpos($extensions,$ext) !== false){
                        //va actualizando md5 de la aplicacion con el md5 de cada archivo
                        $md5_file = md5_file($new);
                        $global_md5 = md5($global_md5 . $md5_file );
                        
                        //agrega archivo al array para mostrarlo en el informe
                        $global_arr[] = $new.',' . "\t" . date("Y-m-d H:i:s", filemtime($new)) .',' . "\t" . format_size(filesize($new)) .',' . "\t" . $md5_file;
                        
                        //agrega cabecera del archivo
                        $fp = fopen($code_file, "a");
                        $name_script = explode(DIRECTORY_SEPARATOR, $new);
                        $type = get_type($ext);
                        $cont++;
                        fwrite($fp, "\n"."\n".'['.$cont.'] ++++++++++++++++++++++++++++ archivo '.$type.': '.$name_script[count($name_script)-1].' +++++++++++++++++++++++++++++' . "\n");
                        fclose($fp);
                        
                        //agrega el codigo fuente del archivo al archivo de codigo fuente total
                        $str_file = file_get_contents($new);
                        file_put_contents($code_file, $str_file, FILE_APPEND | LOCK_EX);
                    }
                }
            }
        }
    }
}

function is_dir_in_array($item){
    global $global_no_dirs;
    
    foreach ($global_no_dirs as $d){
        if(strpos($item,$d) !== false){
            return true;
        }
    }
    return false;
}

function is_file_in_array($item){
    global $global_no_files;
    
    foreach ($global_no_files as $f){
        if(strpos($item,$f) !== false){
            return true;
        }
    }
    return false;
}

//descripcion tipo archivo por su extension
function get_type($extension){
    switch($extension){
    case 'php';
        return 'PHP';
        break;
    case 'hmt';
        return 'HTML';
        break;
    case 'css';
        return 'STYLE';
        break;
    case 'js':
        return 'JAVASCRIPT';
        break;
    default:
        return '';
    }
}

/**
  * obtiene la extension de un archivo
  *
  * @param string $file archivo 1, es estandar
 */
function get_extension_file($file){
    $name = explode('.', $file);
        
    if(count($name) > 1){
        return $name[count($name)-1];
    }else{
        echo $file . "\n";
        return '';
    }
}

function format_size($size, $round = 0) { 
    //Size must be bytes! 
    $sizes = array(' bytes', ' Kb', ' Mb', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'); 
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++){
        $size /= 1024; 
    }
    return round($size,$round).$sizes[$i]; 
}