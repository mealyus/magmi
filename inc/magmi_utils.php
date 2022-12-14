<?php
// utilities function

// return null for empty string
function nullifempty($val)
{
    return (isset($val) ? (trim($val) == "" ? null : $val) : null);
}
// return false for empty string
function falseifempty($val)
{
    return (isset($val) ? (strlen($val) == 0 ? false : $val) : false);
}
// test for empty string
function testempty($arr, $val)
{
    return !isset($arr[$val]) || strlen(trim($arr[$val])) == 0;
}

// place a DELETE maker for empty values
function deleteifempty($val)
{
    return (isset($val) ? (trim($val) == "" ? "__MAGMI_DELETE__" : $val) : "__MAGMI_DELETE__");
}

// convert to array & trims a comma separated list
function csl2arr($cslarr, $sep = ",")
{
    $arr = explode($sep, $cslarr);
    $carr = count($arr);
    for ($i = 0; $i < $carr; $i++) {
        $arr[$i] = trim($arr[$i]);
    }
    return $arr;
}

// trim a list of array values
function trimarray(&$arr)
{
    $carr = count($arr);
    for ($i = 0; $i < $carr; $i++) {
        $arr[$i] = trim($arr[$i]);
    }
}

// Relative value detection (prepend of + or -)
function getRelative(&$val)
{
    $dir = "+";
    if ($val[0] == "-") {
        $val = substr($val, 1);
        $dir = "-";
    } elseif ($val[0] == "+") {
        $val = substr($val, 1);
    }
    return $dir;
}

// Check if we have a remote path
function is_remote_path($path)
{
    $parsed = parse_url($path);
    return isset($parsed['host']);
}

// Returns absolute path for a file with a base path
// if $resolve is set to true,return associated realpath
function abspath($path, $basepath = "", $resolve = true)
{
    if ($basepath == "") {
        $basepath = dirname(dirname(__FILE__));
    }
    $cpath = str_replace('//', '/', $basepath . "/" . $path);
    if ($resolve && !is_remote_path($cpath)) {
        $abs = realpath($cpath);
    } else {
        $inparts = explode("/", $cpath);
        $outparts = array();
        $cinparts = count($inparts);
        for ($i = 0; $i < $cinparts; $i++) {
            if ($inparts[$i] == '..') {
                array_pop($outparts);
            } elseif ($inparts[$i] != '.') {
                $outparts[] = $inparts[$i];
            }
        }
        $abs = implode("/", $outparts);
    }
    return $abs;
}

function truepath($path)
{
    $opath = $path;
    // whether $path is unix or not
    $unipath = strlen($path) == 0 || $path{0}
    != '/';
    // attempts to detect if path is relative in which case, add cwd
    if (strpos($path, ':') === false && $unipath) {
        $path = getcwd() . DIRECTORY_SEPARATOR . $path;
    }
        // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) {
            continue;
        }
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path = implode(DIRECTORY_SEPARATOR, $absolutes);
    // resolve any symlinks
    if (file_exists($path) && linkinfo($path) > 0) {
        $path = readlink($path);
    }
    // put initial separator that could have been lost
    $path = !$unipath ? '/' . $path : $path;
    return $path;
}

// Test for absolute path using OS detection
function isabspath($path)
{
    return ($path[0] == "." || (substr(PHP_OS, 0, 3) == "WIN" && strlen($path) > 1) ? $path[1] == ":" : $path[0] == "/");
}

/*
 * Slugger class, for producing valid url from strings
 */
class Slugger
{
    // Mapping array for intl accented chars
    protected static $_translit = array('??'=>'S','??'=>'s','??'=>'s','??'=>'S','??'=>'Dj','??'=>'Z','??'=>'z','??'=>'z',
        '??'=>'Z','??'=>'z','??'=>'Z','??'=>'A','??'=>'A','??'=>'A','??'=>'A','??'=>'A','??'=>'A','??'=>'A','??'=>'a','??'=>'A',
        '??'=>'C','??'=> 'c','??'=>'C','??'=>'E','??'=>'E','??'=>'E','??'=>'E','??'=>'e','??'=>'E','??'=>'I','??'=>'I','??'=>'I',
        '??'=>'I','??'=>'N','??'=>'n','??'=>'N','??'=>'O','??'=>'O','??'=>'O','??'=>'O','??'=>'O','??'=>'O','??'=>'U','??'=>'U',
        '??'=>'U','??'=>'U','??'=>'Y','??'=>'B','??'=>'Ss','??'=>'a','??'=>'a','??'=>'a','??'=>'a','??'=>'a','??'=>'a','??'=>'a',
        '??'=>'c','??'=>'e','??'=>'e','??'=>'e','??'=>'e','??'=>'i','??'=>'i','??'=>'i','??'=>'i','??'=>'o','??'=>'n','??'=>'o',
        '??'=>'o','??'=>'o','??'=>'o','??'=>'o','??'=>'o','??'=>'u','??'=>'u','??'=>'u','??'=>'y','??'=>'b','??'=>'y','??'=>'f',
        '??'=>'C','??'=>'c','??'=>'L','??'=>'l','??'=>'L','??'=>'l','??'=>'L','??'=>'T','??'=>'t','??'=>'N','??'=>'n','??'=>'R',
        '??'=>'r','??'=>'R','??'=>'r','??'=>'O','??'=>'o','??'=>'U','??'=>'u','??'=>'i', '??'=>'I', '??'=>'u', '??'=>'s', '??'=>'S',
         '??'=>'g', '??'=>'G');

    // Stripping accents
    public static function stripAccents($text)
    {
        return strtr($text, self::$_translit);
    }
    // Slugging function
    public static function slug($str, $allowslash = false)
    {
        $str = strtolower(self::stripAccents(trim($str)));
        $rerep = $allowslash ? '[^a-z0-9-/]' : '[^a-z0-9-]';
        $str = preg_replace("|$rerep|", '-', $str);
        $str = preg_replace('|-+|', "-", $str);
        $str = preg_replace('|-$|', "", $str);
        return $str;
    }
}
