<?php

//写入
function cache_write($name, $var, $values) {
    $cachefile = S_ROOT . './data/data_' . $name . '.php';
    $cachetext = "<?php\r\n" .
            "if(!defined('CHECK_CODE')) exit('Access Denied');\r\n" .
            '$' . $var . '=' . arrayeval($values) .
            "\r\n?>";
    if (!swritefile($cachefile, $cachetext)) {
        exit("File: $cachefile write error.");
    }
}

//数组转换成字串
function arrayeval($array, $level = 0) {
    $space = '';
    for ($i = 0; $i <= $level; $i++) {
        $space .= "\t";
    }
    $evaluate = "array(\n";
    $comma = $space;
    foreach ($array as $key => $val) {
        $key = is_string($key) ? '\'' . addcslashes($key, '\'\\') . '\'' : $key;
        $val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12) ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
        if (is_array($val)) {
            $evaluate .= "$comma$key => " . arrayeval($val, $level + 1);
        } else {
            $evaluate .= "$comma$key => $val";
        }
        $comma = ",\n$space";
    }
    $evaluate .= "\n$space)";
    return $evaluate;
}

//写入文件
function swritefile($filename, $writetext, $openmod = 'w') {
    if (@$fp = fopen($filename, $openmod)) {
        flock($fp, 2);
        fwrite($fp, $writetext);
        fclose($fp);
        return true;
    } else {
        runlog('error', "File: $filename write error.");
        return false;
    }
}

/**
 * 复制目录
 *
 * @param string $src 源目录
 * @param string $dst 目标新目录
 */
function recurse_copy($src, $dst) {  // 原目录，复制到的目录
    $dir = opendir($src);
    @mkdir($dst, 666 , true);
    while (false !== ( $file = readdir($dir))) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

?>
