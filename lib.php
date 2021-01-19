<?php

require_once __DIR__."/vendor/autoload.php";

use \Linker\FileSystem\Core as FS;
use \Linker\FileSystem\Download;
use \Linker\Request\Query;

// Returns the size of a directory in bytes
function dirSize(string $dir) : int {
    $index = FS::scandirTree($dir);
    $size = 0;
    foreach($index as $path) $size += (int) filesize($path);
    return $size;
}

// Convert bytes to human readable size
function FileSizeConvert($bytes) : string {
    $result = "";
    $bytes = floatval($bytes);
        $arBytes = [
            [
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ],
            [
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ],
            [
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ],
            [
                "UNIT" => "KB",
                "VALUE" => 1024
            ],
            [
                "UNIT" => "B",
                "VALUE" => 1
            ],
        ];

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}

// Get directory info
function retrieveInfo(?string $query = NULL)  {
    global $dir,$columns;
    $res = [];
    $dir = rtrim($dir,"/")."/";
    $current = trim($dir.($query ?? ""),"/")."/";
    if(is_dir($current)){
        $scan = FS::scandir($current);
        foreach($scan as $path){
            $tmp = [];
            if(in_array("name",$columns)) 
                $tmp["name"] = pathinfo(basename($path), PATHINFO_FILENAME) != "" 
                    ? pathinfo(basename($path), PATHINFO_FILENAME)
                    : basename($path);
            if(in_array("type",$columns)) 
                $tmp["type"] = is_file($path) 
                    ? "file" 
                    : "directory";
            if(in_array("modified",$columns)) 
                $tmp["modified"] = is_file($path) 
                    ? date("M d, Y h:i A",filemtime($path))
                    : NULL;
            if(in_array("ext",$columns)) 
                $tmp["ext"] = is_file($path) 
                    && pathinfo(basename($path), PATHINFO_EXTENSION) != "" 
                    ? pathinfo(basename($path), PATHINFO_EXTENSION) 
                    : NULL;
            if(in_array("mime",$columns)) 
                $tmp["mime"] = FS::mime_content_type($path) !== FALSE 
                ? FS::mime_content_type($path) 
                : "folder/directory";
            
            
            if(in_array("size",$columns)) 
                $tmp["size"] = is_file($path) 
                ? FileSizeConvert((int) filesize($path)) 
                : FileSizeConvert(dirSize($path));
            
            $tmp["path"] = str_replace("\\","/",$path);
            $tmp["inurl"] = ltrim(str_replace(str_replace("\\","/",$dir),"",str_replace("\\","/",$path)),"/");
            
            $res[] = $tmp;
        }
        return $res;
    } 
    return FALSE;
}

// verify path
function path_type(string $f) : string {
    global $dir;
    $dir    = rtrim($dir,"/")."/";
    $f      = ltrim($f,"/");
    $path   = trim($dir.($f ?? ""),"/");
    return is_file($path) ? "file" : (is_dir($path) ? "dir" : "invalid");
}

function retrieveFile(string $f){
    global $dir;
    if(path_type($f) == "file"){
        $dir    = rtrim($dir,"/")."/";
        $f      = ltrim($f,"/");
        $path   = trim($dir.($f ?? ""),"/");
        $mime   = FS::mime_content_type($path);
        Download::file($path);
    }

    return FALSE;
}

// Filter directory info
function filterRaw(array $data){
    if(isset($data["inurl"])) unset($data["inurl"]);
    if(isset($data["path"])) unset($data["path"]);

    return $data;
}

// Echo document title
function document_title(){
    global $doc_title;
    echo "<title>".htmlentities($doc_title)."</title>";
}

// Echo page title
function page_title(){
    global $page_title;
    echo  '<h1 class="page-title">'.htmlentities($page_title).'</h1>';
}

function favicon(){
    global $favicon;
    echo '<link rel="shortcut icon" href="'.urlencode($favicon).'" type="image/x-icon">';
}
function bread_crumbs($f){
    $res = "Directory: ";
    
    $current = "";

    $levels =[[
        "name"=>"parent",
        "link"=>""
    ]];
    
    foreach(explode("/",urldecode($f)) as $l){
        if($l != ""){
            $levels[] = [
                "name" => $l,
                "link" => $l
            ];
        }
    }
    for($i = 0;$i < count($levels);$i++){
        $current .= rtrim($current."/".$levels[$i]["link"],"/");
        if(($i == 0 && count($levels) <= 1) || $i == (count($levels) - 1)){
            $res .= "<span>".htmlentities($levels[$i]["name"])."</span> / ";
        } else {
            $res .= "<a href='?f=".urlencode($current)."'>".htmlentities($levels[$i]["name"])."</a> / ";
        }
        
    }

    echo "<div class='breadcrumbs'>".rtrim($res," / ")."</div>";
}
function getIconType(string $path){
    $type = "unknown";

    if(is_dir($path)) return "directory";
    if(is_file($path)){
        $type = "file";
        $mime = FS::mime_content_type($path);
        $regex = [
            "mobile"       => "mobile",

            "application"   => "app",
            "image"         => "image",
            "video"         => "video",
            "audio"         => "audio",
            "text"          => "text",
            
            "x-"            => "binary",
            "binary"        => "binary",
            "cgi"           => "app",
            "package"       => "app",
            "javascript"    => "app",
            "script"        => "script",
            "markdown"      => "markdown",
            "md"            => "markdown",
            "book"          => "markdown",
            "doc"           => "document",
            "document"      => "document",
            "word"          => "document",
            "powerpoint"    => "document",
            "excel"         => "document",
            "spreadsheet"   => "document",
            "json"          => "document",
            "android"       => "mobile",
        ];

        foreach($regex as $rgx => $t) if(preg_match('/'.$rgx.'/i',$mime)) $type = $t;
    }
    return $type;
}
function getIcon(string $path){
    global $icon_set;
    $ictype = getIconType($path);
    return $icon_set[$ictype] ?? ($icon_set["unknown"] ?? "❔");
}