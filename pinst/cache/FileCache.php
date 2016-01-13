<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-7
 * Time: 下午5:06
 */

namespace pinst\cache;


use pinst\base\Cache;
use pinst\helper\FileHelper;

class FileCache extends Cache
{
    public $keyPrefix = '';

    public $cachePath = '';

    public $dirMode = 0775;

    public $directoryLevel = 2;

    public $cacheFileSuffix = ".tmp";

    public $gcProbability = 10;

    public $fileMode;

    public function init()
    {
        parent::init();
        $this->cachePath = \Pinst::$app->runtimePath.DIRECTORY_SEPARATOR."cache";
        if (!is_dir($this->cachePath)) {
            FileHelper::createDirectory($this->cachePath,$this->dirMode,true);
        }
    }

    public function exists($key)
    {
        $cacheFile = $this->getCacheFile($this->buildKey($key));
        return @filemtime($cacheFile) > time();
    }

    protected function getCacheFile($key)
    {
        if ($this->directoryLevel > 0) {
            $base = $this->cachePath;
            for ($i = 0; $i < $this->directoryLevel; ++$i) {
                if (($prefix = substr($key, $i + $i, 2)) !== false) {
                    $base .= DIRECTORY_SEPARATOR . $prefix;
                }
            }
            return $base . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
        } else {
            return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;
        }
    }

    protected function getValue($key)
    {
        $cacheFile = $this->getCacheFile($key);

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $cacheValue;
            }
        }

        return false;
    }

    protected function setValue($key, $value, $duration)
    {
        $this->gc();
        $cacheFile = $this->getCacheFile($key);
        if ($this->directoryLevel > 0) {
            @FileHelper::createDirectory(dirname($cacheFile), $this->dirMode, true);
        }
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($cacheFile, $this->fileMode);
            }
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            return @touch($cacheFile, $duration + time());
        } else {
            $error = error_get_last();
            return false;
        }
    }

    public function gc($force = false, $expiredOnly = true)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->gcRecursive($this->cachePath, $expiredOnly);
        }
    }

    protected function gcRecursive($path, $expiredOnly)
    {
        if (($handle = opendir($path)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] === '.') {
                    continue;
                }
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {
                    $this->gcRecursive($fullPath, $expiredOnly);
                    if (!$expiredOnly) {
                        if (!@rmdir($fullPath)) {
                            $error = error_get_last();
                        }
                    }
                } elseif (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time()) {
                    if (!@unlink($fullPath)) {
                        $error = error_get_last();
                    }
                }
            }
            closedir($handle);
        }
    }

}