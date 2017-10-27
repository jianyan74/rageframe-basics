<?php
namespace jianyan\basics\common\library;
use Yii;

/**
 * Class ServerInfo
 * @package common\components
 */
class ServerInfo
{
    /**
     * 服务器信息
     * @var array
     */
    public $server = [
        'ip' => '',//ip
        'domainIP' => '',//服务器域名和IP及进程用户名
        'flag' => '',//服务器标识
        'os' => '',//服务器系统
        'language' => '',//语言
        'name' => '',//名称
        'email' => '',//管理员邮箱
        'webEngine' => '',
        'webPort' => '',//端口
        'webPath' => '',
        'newTime' => '',
        'probePath' => '',
    ];

    /**
     * 系统信息
     * @var array|string|void
     */
    public $sysInfo = [
        'memTotal' => '目前只支持Linux系统',//物理内存总量
        'memFree' => '目前只支持Linux系统',//物理空闲
        'memUsed' => '目前只支持Linux系统', //物理内存使用
        'memPercent' => 0,//物理内存使用百分比
        'memBuffers' => '目前只支持Linux系统',//buffers
        'memCached' => '目前只支持Linux系统',//缓存内存
        'memCachedPercent' => 0,//缓存内存使用百分比
        'memRealUsed' => '目前只支持Linux系统',//真实内存使用
        'memRealFree' => '目前只支持Linux系统',//真实内存空闲
        'memRealPercent' => 0,//真实内存使用百分比
        'swapTotal' => '目前只支持Linux系统',
        'swapFree' => '目前只支持Linux系统',
        'swapUsed' => '目前只支持Linux系统',
        'swapPercent' => 0,
        'uptime' => '目前只支持Linux系统',
        'loadAvg' => '目前只支持Linux系统',
    ];

    /**
     * 硬盘信息
     * @var array
     */
    public $hd = [
        't',//硬盘总量
        'f',//可用
        'u',//已用
        'PCT',//使用率
    ];

    /**
     * 网络信息
     * @var array
     */
    public $netWork;

    /**
     * cpu信息
     * @var string
     */
    public $cpuUse;

    public function __construct()
    {
        $this->server['ip'] = @$_SERVER['REMOTE_ADDR'];
        $domain = $this->os() ? $_SERVER['SERVER_ADDR'] : @gethostbyname($_SERVER['SERVER_NAME']);
        $this->server['domainIP'] = @get_current_user() . ' - ' . $_SERVER['SERVER_NAME'] . '(' . $domain . ')';
        $this->server['flag'] = empty($this->sysInfo['win_n']) ? @php_uname() : $this->sysInfo['win_n'];
        $os = explode(" ", php_uname());
        $oskernel= $this->os() ? $os[2] : $os[1];
        $this->server['os'] = $os[0] . '内核版本：' . $oskernel;
        $this->server['language'] = getenv("HTTP_ACCEPT_LANGUAGE");
        $this->server['name'] =$this->os() ? $os[1] : $os[2];
        $this->server['email'] = @$_SERVER['SERVER_ADMIN'];
        $this->server['webEngine'] = $_SERVER['SERVER_SOFTWARE'];
        $this->server['webPort'] = @$_SERVER['SERVER_PORT'];
        $this->server['webPath'] = $_SERVER['DOCUMENT_ROOT'] ? str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']) : str_replace('\\','/',dirname(__FILE__));
        $this->server['probePath'] =str_replace('\\','/',__FILE__) ? str_replace('\\','/',__FILE__):$_SERVER['SCRIPT_FILENAME'];
        $this->server['newTime'] = date('Y-m-d H:i:s');

        if(!$this->os())
        {
            $this->cpuUse ='目前只支持Linux系统';
        }
        else
        {
            $netWorkOne = $this->getNetWork();
            $cpuOne = $this->getCpuUse();
            sleep(1);
            $netWorkTwo = $this->getNetWork();
            $cpuTwo = $this->getCpuUse();

            $this->netWork = $this->getNetWorkSpeed($netWorkOne,$netWorkTwo);
            $cpu_data = $this->getCpuPercent($cpuOne,$cpuTwo);
            $this->cpuUse =
                'CPU空闲：' . $cpu_data['cpu0']['idle']. "% | " .
                '用户进程：' . $cpu_data['cpu0']['user']. "% | " .
                '内核进程：' . $cpu_data['cpu0']['sys']. "% | " .
                '等待进行I/O：' . $cpu_data['cpu0']['iowait']. "% | " .
                '更改优先级：' . $cpu_data['cpu0']['nice']. "% | " .
                '系统中断：' . $cpu_data['cpu0']['irq']. "% | " .
                '软件中断：' . $cpu_data['cpu0']['softirq']. "%";
        }

        $this->hd = $this->getDisk();
        $this->sysInfo = $this->getSysInfo();
    }

    /**
     * 获取服务器信息
     * @return array|bool
     */
    public function getSysInfo()
    {
        $sysInfo = $this->sysInfo;
        switch(PHP_OS)
        {
            case'Linux':
                $linuxSysInfo = $this->linux();
                $sysInfo = array_merge($sysInfo, $linuxSysInfo);
                break;
            case'WINNT':
                $winntSysInfo = $this->windows();
                break;
        }

        return $sysInfo;
    }

    /**
     * linux服务器信息
     */
    public function linux()
    {
        $res = [];
        //获取CPU信息
        if($cpuinfo = @file("/proc/cpuinfo"))
        {
            $cpuinfo= implode("",$cpuinfo);
            @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s",$cpuinfo, $model);//CPU 名称
            @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/",$cpuinfo, $mhz);//CPU频率
            @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/",$cpuinfo, $cache);//CPU缓存
            @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/",$cpuinfo, $bogomips);

            if(is_array($model[1]))
            {
                $cpunum = count($model[1]);

                $mhz[1][0] =' | 频率:'.$mhz[1][0];
                $cache[1][0] =' | 二级缓存:'.$cache[1][0];
                $bogomips[1][0] =' | Bogomips:'.$bogomips[1][0];
                $res['cpu']['num'] = $cpunum;
                $res['cpu']['model'][] = $model[1][0].$mhz[1][0].$cache[1][0].$bogomips[1][0];
                if(is_array($res['cpu']['model']))$res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
                // if(is_array($res['cpu']['mhz']))$res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
                //  if(is_array($res['cpu']['cache']))$res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
                //  if(is_array($res['cpu']['bogomips']))$res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
            }
        }

        //运行时间
        if($uptime = @file("/proc/uptime"))
        {
            $str = explode(" ", implode("",$uptime));
            $str = trim($str[0]);
            $min = $str / 60;
            $hours= $min / 60;
            $days= floor($hours/24);
            $hours= floor($hours-($days*24));
            $min= floor($min-($days*60*24)-($hours*60));
            $res['uptime'] = $days . " 天 " . $hours . " 小时 " . $min . " 分钟 ";
        }

        //内存
        $meminfo = @file("/proc/meminfo");
        if($meminfo)
        {
            $meminfo = implode("",$meminfo);
            preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s",$meminfo, $buf);
            preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s",$meminfo, $buffers);
            $resmem['memTotal'] = round($buf[1][0] / 1024, 2);
            $resmem['memFree'] = round($buf[2][0] / 1024, 2);
            $resmem['memBuffers'] = round($buffers[1][0] / 1024, 2);
            $resmem['memCached'] = round($buf[3][0] / 1024, 2);
            $resmem['memUsed'] = $resmem['memTotal'] - $resmem['memFree'];
            $resmem['memPercent'] = (floatval($resmem['memTotal']) != 0) ? round($resmem['memUsed'] / $resmem['memTotal'] * 100, 2) : 0;
            $resmem['memRealUsed'] = $resmem['memTotal'] - $resmem['memFree'] - $resmem['memCached'] - $resmem['memBuffers'];
            $resmem['memRealFree'] = $resmem['memTotal'] - $resmem['memRealUsed'];
            $resmem['memRealPercent'] = (floatval($resmem['memTotal']) != 0 ) ? round($resmem['memRealUsed'] / $resmem['memTotal'] * 100,2) : 0 ;
            $resmem['memCachedPercent'] = (floatval($resmem['memCached']) != 0) ? round($resmem['memCached'] / $resmem['memTotal'] * 100,2) : 0;
            $resmem['swapTotal'] = round($buf[4][0] / 1024, 2);
            $resmem['swapFree'] = round($buf[5][0] / 1024, 2);
            $resmem['swapUsed'] = round($resmem['swapTotal'] - $resmem['swapFree'], 2);
            $resmem['swapPercent'] = (floatval($resmem['swapTotal']) != 0) ? round($resmem['swapUsed'] / $resmem['swapTotal'] * 100,2) : 0;
            $resmem = $this->formatter($resmem);
            $res = array_merge($res,$resmem);
        }

        // LOAD AVG 系统负载
        $loadavg = @file("/proc/loadavg");
        if($loadavg)
        {
            $loadavg = explode(" ", implode("",$loadavg));
            $loadavg = array_chunk($loadavg, 4);
            $res['loadAvg'] = implode(" ", $loadavg[0]);
        }

        return $res;
    }

    public function windows()
    {
        return true;
    }

    /**
     * 时间输出
     * @param $resmem
     * @return mixed
     */
    public function formatter($resmem)
    {
        $array = ['memPercent','memRealPercent','memCachedPercent','swapPercent'];
        foreach ($resmem as $key => &$item)
        {
            if(!in_array($key, $array))
            {
                $item = $item .' M';
            }
        }

        return $resmem;
    }

    /**
     * 获取cpu信息
     * @return array
     */
    public function getCpuUse()
    {
        $data= @file('/proc/stat');
        $cores= [];
        foreach($data as $line)
        {
            if(preg_match('/^cpu[0-9]/',$line))
            {
                $info= explode(' ',$line);
                $cores[] = [
                    'user' => $info[1],
                    'nice' => $info[2],
                    'sys' => $info[3],
                    'idle' => $info[4],
                    'iowait' => $info[5],
                    'irq' => $info[6],
                    'softirq' => $info[7]
                ];
            }
        }

        return $cores;
    }

    /**
     * 对比CPU使用率
     * @param $cpuOne
     * @param $cpuTwo
     * @return bool
     */
    public function getCpuPercent($cpuOne,$cpuTwo)
    {
        $num = count($cpuOne);
        if($num !== count($cpuTwo))
        {
            return false;
        };

        $cups= [];
        for($i = 0; $i < $num; $i++)
        {
            $dif = [];
            $dif['user'] = $cpuTwo[$i]['user'] - $cpuOne[$i]['user'];
            $dif['nice'] = $cpuTwo[$i]['nice'] - $cpuOne[$i]['nice'];
            $dif['sys'] = $cpuTwo[$i]['sys'] - $cpuOne[$i]['sys'];
            $dif['idle'] = $cpuTwo[$i]['idle'] - $cpuOne[$i]['idle'];
            $dif['iowait'] = $cpuTwo[$i]['iowait'] - $cpuOne[$i]['iowait'];
            $dif['irq'] = $cpuTwo[$i]['irq'] - $cpuOne[$i]['irq'];
            $dif['softirq'] = $cpuTwo[$i]['softirq'] - $cpuOne[$i]['softirq'];
            $total = array_sum($dif);

            $cpu = [];
            foreach($dif as $x => $y)
            {
                $cpu[$x] = round($y / $total * 100, 2);
                $cpus['cpu'.$i] = $cpu;
            }
        }

        return $cpus;
    }

    /**
     * 获取硬盘情况
     * @return mixed
     */
    public function getDisk()
    {
        $d['t'] = round(@disk_total_space(".") / (1024*1024*1024),3);
        $d['f'] = round(@disk_free_space(".") / (1024*1024*1024),3);
        $d['u'] = $d['t'] - $d['f'];
        $d['PCT'] = (floatval($d['t']) != 0) ? round($d['u'] / $d['t'] * 100, 2) : 0;

        $d['PCT'] = round($d['PCT'], 2);
        $d['u'] = round($d['u'], 2);

        return $d;
    }

    /**
     * 网卡流量
     * @return mixed
     */
    public function getNetWork()
    {
        $res = [];
        $res['allOutSpeed']  = 0;
        $res['allInputSpeed']  = 0;

        $strs = @file("/proc/net/dev");
        $lines = count($strs);
        for($i = 2;$i < $lines;$i++)
        {
            preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info);

            $name = $info[1][0];
            $res[$name]['name'] = $name;
            $res[$name]['outSpeed'][$i] = $info[10][0];
            $res[$name]['inputSpeed'][$i] = $info[2][0];

            $res['allOutSpeed'] += $info[10][0];
            $res['allInputSpeed'] += $info[2][0];
        }

        return $res;
    }

    /**
     * 计算出入网速和总量
     * @param $netWorkOne
     * @param $netWorkTwo
     */
    public function getNetWorkSpeed($netWorkOne,$netWorkTwo)
    {
        $netWork = $netWorkTwo;
        $netWork['currentOutSpeed'] = $netWorkTwo['allOutSpeed'] - $netWorkOne['allOutSpeed'];
        $netWork['currentInputSpeed'] = $netWorkTwo['allInputSpeed'] - $netWorkOne['allInputSpeed'];

        return $netWork;
    }

    /**
     * @return bool
     */
    public function os()
    {
        return DIRECTORY_SEPARATOR == '/' ? true : false;
    }
}