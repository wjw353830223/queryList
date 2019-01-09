<?php
namespace app\index\command;

use GuzzleHttp\Client;
use QL\QueryList;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Request;

class Query extends Command
{
    protected $limit = 100;//每次循环限制100条 防止内存溢出
    protected function configure(){
        $this->setName('query')->setDescription('query data');
    }

    protected function execute(Input $input, Output $output){
        $request = new Request();
        $request->setModule("index");
        $this->_query();
    }
    //获取svg path列表
    private function _query(){
        $url = 'http://www.query.com/index/index/svg';
        $client = new Client();
        $res = $client->request('GET', $url, [
            'wd' => 'QueryList'
        ]);
        $html = (string)$res->getBody();
        $rules = [
            'class' => ['#full-body>path','class'],
            'fill'=>['#full-body>path','fill'],
            'fill-rule'=>['#full-body>path','fill-rule'],
            'd'=>['#full-body>path','d'],
            'opacity'=>['#full-body>path','opacity'],
            'svg-tooltip'=>['#full-body>path','svg-tooltip'],
            'transform'=>['#full-body>path','transform'],
            'ngClick'=>['#full-body>path','ng-click']
        ];
        $ql=QueryList::getInstance();
        $rt = $ql->html($html)->rules($rules)->query()->getData(function($item){
            $pattern="/^[^']+'([\w-]+)'[^\d]+(\d)+\)$/";
            if(preg_match($pattern,$item['ngClick'],$match)){
                $item['name']=$match[1];
                $item['level']=$match[2]+1;
            }
            $class=explode(' ',$item['class']);
            $length=count($class);
            $item['direction'] = 'front';
            if($class[1]=='layer-back-2' || $class[1]=='layer-back-1'){
                $item['direction']='back';
            }
            if($length==5){
                $item['sex'] = $class[4];
                $item['parentName'] = $class[2];
            }
            if($length==4){
                $item['sex'] = $class[3];
                if($item['level']==2){
                    $person=json_decode(file_get_contents('person.json'),true);
                    if($item['sex']=='female'){
                        $person=[$person['feMaleZ'],$person['feMaleF']];
                    }else{
                        $person=[$person['maleF'],$person['maleZ']];
                    }
                    if(in_array($item['name'],['neck','right-breast','left-breast','pelvis'])){
                        if($item['name']=='neck'){
                            $item['parentName'] = 'neck';
                        }
                        if($item['name']=='right-breast' || $item['name']=='left-breast'){
                            $item['parentName'] = 'chest';
                        }
                        if($item['name']=='pelvis'){
                            $item['parentName'] = 'pelvis';
                        }
                    }else{
                        foreach($person as $direction=>$value){
                            foreach($value as $key=>$val){
                                foreach($val as $kk=>$vv){
                                    $part=explode('.',$vv);
                                    $name = array_shift($part);
                                    if($name==$item['name']){
                                        $item['parentName'] = $key;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }

                }
            }
            $item['fillRule']=$item['fill-rule'];
            $item['svgTooltip']=$item['svg-tooltip'];
            return $item;
        });
        $data=[];
        foreach($rt as $key=>$val){
            if($val['level']==1){
                $data[$val['sex']][$val['direction']]['levelOne'][]=$val;
            }
            if($val['level']==2){
                $data[$val['sex']][$val['direction']]['levelTwo'][]= $val;
            }
        }
        foreach($data as $key=>$value){
            foreach($value as $kk=>$val){
               foreach ($val['levelTwo'] as $kkk=>$vvv){
                   if(empty($vvv['parentName'])){
                        echo 'parentName为空'.PHP_EOL;
                        print_r($vvv);
                   }
               }
            }
        }
        file_put_contents('svgPath.json',json_encode($data));
    }
}



