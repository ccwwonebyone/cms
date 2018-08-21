<?php
function do_dir($dir)
{
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if($file == '..' || $file == '.') continue;
                if(is_dir($dir.'/'.$file)){
                    $cdir = $dir.'/'.$file;
                    do_dir($cdir);
                }else{
                    parseData($dir.'/'.$file);
                }
            }
            closedir($dh);
        }
    }
}

function parseData($filepath)
{
    $content = file_get_contents($filepath);

    if(strpos($content, 'gzinflate') === false || strpos($filepath, 'decode.php') !== false || strpos($filepath, 'temp.php') !== false || strpos($filepath, 'plugins') !== false){
        echo "跳过：".$filepath."\r\n";
        return false;
    }
    $content = str_replace('eval(', 'echo"', $content);
    $content = str_replace(')))', '))"', $content);
    file_put_contents('temp.php', $content);
    exec('php temp.php',$data);
    $data = implode("\r\n", $data);
    $data = str_replace("base64_decode(", 'base64_decode("', $data);
    $data = str_replace("))", '"))', $data);
    file_put_contents('temp.php', "<?php\r\necho ".$data.';');
    exec('php temp.php',$data);
    $data = implode("\r\n", $data);
    file_put_contents($filepath, "<?php".$data);
    echo '解析：'.$filepath."\r\n";
    return true;
}
parseData('./index.php');