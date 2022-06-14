<?php
require('debug.php');
require('src/netEaseSource.php');

$downloader = (new ReflectionClass('AumNetEaseSource'))->newInstance();
$testArray = array(
    array('title' => '无法停止爱你', 'artist' => '贺仙人'),
    array('title' => 'Boogie Up', 'artist' => '宇宙少女')
);

foreach ($testArray as $key => $item) {
    echo "\n++++++++++++++++++++++++++++++\n";
    echo "测试 $key 开始...\n";
    if ($key > 0) {
        echo "等待 5 秒...\n";
        sleep(5);
    }
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
