<?php
/**
 * @author donknap
 * @date 18-7-19 上午10:49
 */

use Swoole\Coroutine;

if (!function_exists('getApp')) {
	/**
	 * 获取加载器
	 * @return \W7\Core\Helper\Loader
	 */
	function iloader() {
		return \W7\App::getLoader();
	}
}

if (!function_exists('istudly')) {
	function istudly($value) {
		$value = ucwords(str_replace(['-', '_'], ' ', $value));
		return str_replace(' ', '', $value);
	}
}

if (!function_exists('ioutputer')) {
	/**
	 * 获取输出对象
	 * @return W7\Console\Io\Output
	 */
	function ioutputer() {
		return iloader()->singleton(\W7\Console\Io\Output::class);
	}
}

if (!function_exists('iinputer')) {
	/**
	 * 输入对象
	 * @return W7\Console\Io\Input
	 */
	function iinputer() {
		return iloader()->singleton(\W7\Console\Io\Input::class);
	}
}

if (!function_exists('iconfig')) {
	/**
	 * 输入对象
	 * @return W7\Core\Config\Config
	 */
	function iconfig() {
		return iloader()->singleton(\W7\Core\Config\Config::class);
	}
}
if (!function_exists('isMac')) {
    /**
     * 是否是mac环境
     *
     * @return bool
     */
     function isMac(): bool
    {
        return \stripos(PHP_OS, 'Darwin') !== false;
    }
}
if (!function_exists('isCo'))
{
    /**
     * 是否是在协成
     * @return bool
     */
    function isCo():bool
    {
        return Coroutine::getuid()>0;
    }
}
