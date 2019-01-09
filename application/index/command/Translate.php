<?php
namespace app\index\command;

use GuzzleHttp\Client;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Request;

class Translate extends Command
{
    protected $limit = 100;//每次循环限制100条 防止内存溢出
    protected function configure(){
        $this->setName('translate')->setDescription('translate english to chinese!');
    }

    protected function execute(Input $input, Output $output){
        $request = new Request();
        $request->setModule("index");
//        $res=$this->_svgPath();
//        file_put_contents('svgPathNew.json',json_encode($res));
        $this->_json();
    }
    //获取svg path列表
    private function _svgPath(){
        $source = json_decode(file_get_contents('svgPath.json'),true);
        foreach($source as $sex=>&$value){
            foreach($value as $deriction=>&$val){
                foreach($val as $level=>&$vv){
                    foreach($vv as &$item){
                        $res=$this->_translate($item['name']);
                        $translation=$item['name'];
                        if($res['errorCode']==0){
                            $translation=array_pop($res['translation']);
                            echo 'success!'.PHP_EOL;
                        }else{
                            echo '翻译'.$sex.' '.$deriction.' '.$level.' '.$item['name'].'出错！错误码：'.$res['errorCode'].PHP_EOL;
                        }
                        $item['hanzi']=$translation;
                    }
                    unset($item);
                }
                unset($vv);
            }
            unset($val);
        }
        unset($value);
        return $source;
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
    //获取json列表并翻译
    private function _json(){
        $dir=$this->_query('F:\XAMPP\htdocs\queryList\json');
        foreach($dir['dir'] as $key=>$value){
            $source_dir = 'json'.DIRECTORY_SEPARATOR . $key;
            $dir = 'bodyJson'.DIRECTORY_SEPARATOR . $key;
            if(!is_dir($dir)){
                @mkdir($dir,0777,true);
            }
           if(isset($value['file'])){
               foreach($value['file'] as $val){
                   $json=file_get_contents($source_dir.DIRECTORY_SEPARATOR.$val);
                   $arr = json_decode($json,true);
                   foreach($arr as $name=>&$vv){
                       $res=$this->_translate($vv['Name']);
                       $translation=$vv['Name'];
                       if($res['errorCode']==0){
                           $translation=array_pop($res['translation']);
                           echo 'success!'.PHP_EOL;
                       }else{
                           echo '翻译'.$source_dir.$val.'出错！错误码：'.$res['errorCode'].PHP_EOL;
                       }
                       $vv['hanzi']=$translation;
                   }
                   unset($vv);
                   file_put_contents($dir.DIRECTORY_SEPARATOR.$val,json_encode($arr));
               }
           }
           if(isset($value['dir'])){
               foreach($value['dir'] as $part=>$val){
                   $source_dir = 'json'.DIRECTORY_SEPARATOR . $key .DIRECTORY_SEPARATOR . $part;
                   $dir = 'bodyJson'.DIRECTORY_SEPARATOR . $key .DIRECTORY_SEPARATOR . $part;
                   if(!is_dir($dir)){
                       @mkdir($dir,0777,true);
                   }
                   if(isset($val['file'])){
                       foreach($val['file'] as $kk=>$vv){
                           $json=file_get_contents($source_dir.DIRECTORY_SEPARATOR.$vv);
                           $arr = json_decode($json,true);
                           foreach($arr as $name=>&$vvv){
                               $res=$this->_translate($vvv['Name']);
                               $translation=$vvv['Name'];
                               if($res['errorCode']==0){
                                   $translation=array_pop($res['translation']);
                                   echo 'success!'.PHP_EOL;
                               }else{
                                   echo '翻译'.$source_dir.$vvv.'出错！错误码：'.$res['errorCode'].PHP_EOL;
                               }
                               $vvv['hanzi']=$translation;
                           }
                           unset($vvv);
                           file_put_contents($dir.DIRECTORY_SEPARATOR.$vv,json_encode($arr));
                       }
                   }
               }
           }
        }
    }
    //有道智云翻译
    private function _translate($q){
        $url = 'http://openapi.youdao.com/api';
        $client = new Client();
        $salt=$this->GetRandStr(4);
        $appKey='39a31df6172d2c29';
        $appSecret='LazB7fQGxqSV239oQorpea0XhmmrhEDp';
        $signStr=$appKey.$q.$salt.$appSecret;
        $sign=md5($signStr);
        $query=[
            'q'=>$q,
            'from'=>'EN',
            'to'=>'zh_CHS',
            'appKey'=>$appKey,
            'salt'=>$salt,
            'sign'=>$sign
        ];
        $res = $client->request('GET', $url, [
            'verify' => false,
            'query' => $query,
            'headers' => [
                'accept-encoding' => 'gzip, deflate, br',
                'accept' => 'application/json'
            ]]);
        $res = (string)$res->getBody();
        $res=json_decode($res,true);
        return $res;
    }
    private function GetRandStr($len)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i=0; $i<$len; $i++)
        {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }
}



