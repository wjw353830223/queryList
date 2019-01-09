<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\Request;

class GetDir extends Command
{
    protected $limit = 100;//每次循环限制100条 防止内存溢出
    protected function configure(){
        $this->setName('get:dir')->setDescription('list the files of a dir');
        $this->addArgument('path', Argument::REQUIRED, "The name of the local dir is required!");
    }

    protected function execute(Input $input, Output $output){
        $request = new Request();
        $request->setModule("index");
        $path = $input->getArgument('path');
        $dir=$this->_query($path);
        print_r($dir);
        $arr=$this->_parseDir($dir);
        file_put_contents('person.json',json_encode($arr));
    }
    //获取文件病症映射
    private function _query($dir){
        $result = array();
        $handle = opendir($dir);//读资源
        if ($handle){
            while (($file = readdir($handle)) !== false ){
                if ($file != '.' && $file != '..'){
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    $arrs = explode(DIRECTORY_SEPARATOR,$cur_path);
                    $last=array_pop($arrs);
                    if (is_dir($cur_path )){//判断是否为目录，递归读取文件
                        $result['dir'][$last] = $this->_query($cur_path );
                    }else{
                        $result['file'][] = $last;
                    }
                }
            }
            closedir($handle);
        }
        return $result;
    }
    private function _parseDir($dir){
        $arr=[];
        foreach($dir['dir'] as $key=>$value){
            foreach($value['dir'] as $kk=>$vv){
                $arr[$key][$kk]=$vv['file'];
            }
        }
        return $arr;
    }

}



