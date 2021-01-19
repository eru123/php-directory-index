<?php

$dir            =  __DIR__;

// Available columns - (name|ext|size|mime|type|modified)
$columns        = ["name","ext","size","mime","type","modified"];

$doc_title      = "PHP File Indexer";
$page_title     = "Index of /";
$favicon        =  "favicon.png";
$injected_css   = ["style.css"];

$icon_set = [
    "directory" => "📁",
    "file"      => "📝",
    "document"  => "📄",
    "video"     => "🎞️",
    "audio"     => "🎧",
    "image"     => "📷",
    "text"      => "📝",
    "binary"    => "🔢",
    "app"       => "📦",
    "mobile"    => "📱",
    "script"    => "📜",
    "program"   => "💾",
    "markdown"  => "📖",
    "unknown"   => "🗑️"
];