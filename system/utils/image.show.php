<?php

class IMG
{
    public function show($path, $name)
    {
        if (substr_count($name, 'giaophanthaibinh.org')) {
            return $name;
        }

        return ROOT_URL . $path . $name;

    }

    public function thumb($row)
    {
        return IMAGE_URL . 'data/news/' . date('Y/n/j',
                $row['time_created']) . '/' . $row['img1'];
    }

    public function showImgFrontend($row)
    {
        return IMAGE_URL . 'data/news/' . date('Y/n/j/',
                (int)$row['time_created']) . $row['img1'];
    }
}