<?php
require('debug.php');
require('src/netEaseSource.php');

$downloader = (new ReflectionClass('AumNetEaseSource'))->newInstance();
$testArray = array(
    array('title' => '无法停止爱你', 'artist' => '贺仙人'),
    array('title' => 'Boogie Up', 'artist' => '宇宙少女'),
    array('title' => '나 혼자 여름 [Piano Ver.]', 'artist' => 'Brave Girls'),
    array('title' => 'tell your world', 'artist' => '初音ミク'),
    array('title' => 'tell your world', 'artist' => 'livetune / 初音ミク'),
    array('title' => 'Mood (Remix)', 'artist' => '24KGoldn&Justin Bieber&J Balvin&iann dior'),
    array('title' => 'WOW', 'artist' => 'Dom.T')
);

foreach ($testArray as $key => $item) {
    echo "\n++++++++++++++++++++++++++++++\n";
    echo "测试 $key 开始...\n";
    if ($key > 0) {
        echo "等待 5 秒...\n";
        sleep(5);
    }
    echo "{title = " . $item['title'] . "; artist = " . $item['artist'] . " }.\n";
    $testObj = new AudioStationResult();
    $count = $downloader->getLyricsList($item['artist'], $item['title'], $testObj);
    if ($count > 0) {
        $item = $testObj->getFirstItem();
        $downloader->getLyrics($item['id'], $testObj);
    } else {
        echo "没有查找到任何歌词！\n";
    }
    echo "测试 $key 结束。\n";
}
