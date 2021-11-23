<?php

namespace App\Service;

use App\Model\Banner;
use App\Model\Help;
use App\Model\HelpClass;
use App\Model\News;
use App\Model\NewsClass;

class HomeService
{
    public static function GetBanner(): array
    {
        return Banner::create()->where('status', 1)->order('sort')->all();
    }


    public static function GetHelpItem($help_id)
    {
        return Help::create()->get(['id' => $help_id]);
    }

    public static function GetHelpList($class_id = null, $page = 1, $size = 10): array
    {
        if (!$class_id) {
            $news_class = HelpClass::create()->all();
            $news_array = [];
            foreach ($news_class as $key => $value) {
                $news = $value->toArray();
                $news['children'] = Help::create()->order('id')->limit(5)->all();
                $news_array [] = $news;
            }
            return $news_array;
        }
        return Help::create()->where('class_id', $class_id)->order('id')->limit($size * ($page - 1), $size)->all();
    }

    public static function GetNewsItem($news_id)
    {
        return News::create()->get(['id' => $news_id]);
    }

    public static function GetNewsList($class_id = null, $page = 1, $size = 10): array
    {
        if (!$class_id) {
            $news_class = NewsClass::create()->all();
            $news_array = [];
            foreach ($news_class as $key => $value) {
                $news = $value->toArray();
                $news['children'] = News::create()->order('id')->limit(5)->all();
                $news_array [] = $news;
            }

            return $news_array;
        }
        return News::create()->where('class_id', $class_id)->order('id')->limit($size * ($page - 1), $size)->all();
    }
}