<?php
/**
 * @author donknap
 * @date 18-7-25 下午3:03
 */

namespace W7\Core\Process;

use W7\App;
use W7\Core\Base\ProcessInterface;
use W7\Core\Base\Reload;

class ReloadProcess implements ProcessInterface {

    /**
     * 监听文件变化的路径
     *
     * @var string
     */
    private $watchDir;

    /**
     * the lasted md5 of dir
     *
     * @var string
     */
    private $md5File = '';

    /**
     * the interval of scan
     *
     * @var int
     */
    private $interval = 3;

    /**
     * 初始化方法
     */
    public function __construct()
    {
        $pathConfig = iconfig()->getUserConfig('define');
        $this->watchDir = APP_PATH;
        $this->md5File = FileHelper::md5File($this->watchDir);
    }
    public function check() {
        $serverConfig = iconfig()->getServer();
        if (!$serverConfig['autoReload'])
        {
            return true;
        }
        return true;
    }

    public function run()
    {
        $pname = App::$server->getPname();
        $processName = sprintf('%s reload process', $pname);
        /**
         * @var Reload $reload
         */
        $reload = iloader()->singleton(Reload::class);
        $reload->run();
    }
}