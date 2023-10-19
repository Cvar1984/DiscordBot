<?php

namespace Cvar1984\DiscordBot;

class Quran
{
    protected static function request($url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    public static function listChapter() : array
    {
        $res = self::request('https://api.npoint.io/99c279bb173a6e28359c/data');
        $res = json_decode($res, true);
        return $res;
    }
    public static function listVerseByChapter(int $chapter) : array
    {
        $res = self::request(sprintf('https://api.npoint.io/99c279bb173a6e28359c/surat/%s', $chapter));
        $res = json_decode($res, true);
        return $res;
    }
}

//print_r(Quran::listAllChaper());
//print_r(Quran::listVerseByChapter(1));