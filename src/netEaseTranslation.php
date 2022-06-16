<?php

class AumNetEaseTranslation
{
    private $orgLrc;
    private $transLrc;
    public function __construct($orgLrc, $transLrc) {
        $this->orgLrc = $orgLrc;
        $this->transLrc = $transLrc;
    }

    private function getLrcTime($str) {
        $key = strstr($str, ']', true);
        if ($key === false) {
            return '';
        }

        return $key . ']';
    }

    private function getLrcText($str, $key) {
        if ($key === '') {
            return $str;
        }

        return str_replace($key, '', $str);
    }

    private function isValidLrcTime($str)
    {
        if (trim($str) === '' || $str[0] !== '[') {
            return false;
        }

        $keyLen = strlen($str);
        if ($keyLen < 9 || $keyLen > 11) {
            return false;
        }
        for ($count = 1; $count < $keyLen - 1; $count++) {
            $ch = $str[$count];
            if ($ch !== ':' && $ch !== '.' && !is_numeric($ch)) {
                return false;
            }
        }

        return true;
    }

    private function isValidLrcText($str)
    {
        if (trim($str) === '' || trim($str) === '//') {
            return false;
        }
        return true;
    }

    private function getTimeFromTag($tag)
    {
        $min = substr($tag, 1, 2);
        $sec = substr($tag, 4, 2);
        $mil = substr($tag, 7, strlen($tag) - 8);
        return $mil + $sec * 1000 + $min * 60 * 1000;
    }

    // 若 $lTime > $rTime, 返回 1; $lTime < $rTime, 返回 -1; $lTime == $rTime, 返回 0
    private function compareTime($lTime, $rTime) {
        $subVal = $lTime - $rTime;
        // 允许有 1ms 的误差
        if ($subVal > 1) {
            return 1;
        } elseif ($subVal < -1) {
            return  -1;
        } else {
            return 0;
        }
    }

    private function processLrcLine($lrc)
    {
        $result = array();
        foreach (explode("\n", $lrc) as $line) {
            $line = trim($line);
            $key = $this->getLrcTime($line);
            $value = $this->getLrcText($line, $key);
            if (!$this->isValidLrcTime($key) || !$this->isValidLrcText($value)) {
                $key = '';
                $value = $line;
            }
            array_push($result, array('tag' => $key, 'lrc' => $value));
        }
        return $result;
    }

    public function getChineseTranslationLrc()
    {
        $resultLrc = '';
        $orgLines = $this->processLrcLine($this->orgLrc);
        $transLines = $this->processLrcLine($this->transLrc);

        $transCursor = 0;
        foreach ($orgLines as $line) {
            $key = $line['tag'];
            $value = $line['lrc'];
            $resultLrc .= $key . $value;

            $trans = '';
            if ($key !== '') {
                $time = $this->getTimeFromTag($key);
                for ($i = $transCursor; $i < count($transLines); $i++) {
                    $tKey = $transLines[$i]['tag'];
                    if ($tKey === '') {
                        continue;
                    }

                    $tTime = $this->getTimeFromTag($tKey);
                    if ($this->compareTime($tTime, $time) > 0) {
                        $transCursor = $i;
                        break;
                    }

                    $tValue = $transLines[$i]['lrc'];
                    if ($this->compareTime($tTime, $time) == 0) {
                        $transCursor = $i + 1;
                        $trans = $tValue;
                        break;
                    }
                }
            }

            if ($trans !== '') {
                $resultLrc .= " 【{$trans}】";
            }
            $resultLrc .= "\n";
        }
        return $resultLrc;
    }
}