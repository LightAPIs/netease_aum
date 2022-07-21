<?php
require('netEaseTranslation.php');

class AumNetEaseHandler {
    public static $siteSearch = 'https://music.163.com/api/search/get/web';
    public static $siteDownload = 'https://music.163.com/api/song/lyric?os=pc&lv=-1&kv=0&tv=-1&id=';
    public static $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36';

    public static function getContent($url, $defaultValue, $isPost = false, $postParams = null) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($curl, CURLOPT_POST, $isPost);
        if ($isPost) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postParams));
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            return $defaultValue;
        } else {
            return $result;
        }
    }

    public static function search($title, $artist) {
        $results = array();
        $params = array(
            's' => $title . " " . $artist,
            'offset' => '0',
            'limit' => '20',
            'type' => '1' // 搜索单曲(1)，歌手(100)，专辑(10)，歌单(1000)，用户(1002)
        );

        $jsonContent = self::getContent(self::$siteSearch, '{"result":{"songs":[]}}', true, $params);
        $json = json_decode($jsonContent, true);

        $songArray = $json['result']['songs'];
        $idArray = array();
        foreach($songArray as $songItem) {
            if (in_array($songItem['id'], $idArray)) {
                # 排除重复项
                continue;
            }
            $song = $songItem['name'];
            $id = $songItem['id'];
            array_push($idArray, $id);
            $singers = array();
            foreach ($songItem['artists'] as $singer) {
                array_push($singers, $singer['name']);
            }
            $des = $songItem['album']['name'];

            array_push($results, array('song' => $song, 'id' => $id, 'singers' => $singers, 'des' => $des));
        }
        return $results;
    }

    public static function downloadLyric($songId) {
        $url = self::$siteDownload . $songId;
        $jsonContent = self::getContent($url, '{"lrc":{"lyric":""},"tlyric":{"lyric":""}}');
        $json = json_decode($jsonContent, true);
        $lyric = $json['lrc']['lyric'];
        // Chinese translation
        $transLyric = $json['tlyric']['lyric'];
        if (strlen($transLyric) > 0) {
            $tl = new AumNetEaseTranslation($lyric, $transLyric);
            $lyric = $tl->getChineseTranslationLrc();
        }
        return $lyric;
    }
}
