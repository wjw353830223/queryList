<?php
namespace app\index\command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception;
use function GuzzleHttp\Psr7\str;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Request;

class Captch extends Command
{
    protected $limit = 100;//每次循环限制100条 防止内存溢出
    protected function configure(){
        $this->setName('captch')->setDescription('captch data');
    }

    protected function execute(Input $input, Output $output){
        $request = new Request();
        $request->setModule("index");
        $this->_captch();
    }
    //抓取数据
    private function _captch(){
        $url = 'https://symptoms.webmd.com/search/2/api/scbodytypeahead';
        $client = new Client();
        $body=json_decode(file_get_contents('bodyData.json'),true);
        $query = [
            'q' => '',
            'cache_2' => 'true',
            'count' => '1000',
            'gender' =>'F',
        ];
        foreach($body as $key=>$value){
            if($key=='maleZ' || $key=='maleF'){
                $query['gender'] = 'M';
            }
            $dir = 'bodyJson'.DIRECTORY_SEPARATOR . $key;
            if(!is_dir($dir)){
                @mkdir($dir,0777,true);
            }
            foreach($value as $kk=>$val){
                $dir1 = 'bodyJson'.DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR .$val['name'];
                if(!is_dir($dir1) && !in_array($val['name'],['neck','skin','general'])){
                    @mkdir($dir1,0777,true);
                }
                if(!empty($val['parts'])){
                    foreach($val['parts'] as $kkk=>$vvv){
                        $filename = 'bodyJson'.DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR .$val['name'] . DIRECTORY_SEPARATOR . $vvv['name'] . ".json";
                        $query['part'] = $vvv['id'];
                        try{
                            $res = $client->request('GET', $url, [
                                'verify' => false,
                                'query' => $query,
                                'headers' => [
                                    'postman-token' => '0e5363bf-4f43-5682-d0b7-c4b00bbf4250',
                                    'cache-control' => 'no-cache',
                                    'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                                    'timestamp' => 'Thu, 20 Dec 2018 03:28:05 GMT',
                                    'referer' => 'https://symptoms.webmd.com/default.htm?target=body',
                                    'if-none-match' => 'W/\\"4010-UcBlYABprNMWyip9M3AyMg\\"',
                                    'enc_data' => '6X68dnANX9yQVYVK2kWqLgOjlspHRm22wsTpF9eU4Rw=',
                                    'cookie' => '__cfduid=d16280eb3aec66e4bffcdec5d1346d8f01545266879; VisitorId=412d4002-3a4e-40f2-a472-f73707a81a11; cmt=; ck_consent=true; s_vi=[CS]v1|2E0D736085311B5D-40000121E0011CC1[CE]; gdpreu=eu%7CCHN%7CNOTIN%7C1545266880; mf_user=5693dfb83bf080c0e29d4c2a45f4fd56|; turn=2500805827616571407|181220|y; __gads=ID=8dbb5eb2820cc9f6:T=1545266883:S=ALNI_MZKi-3ou8uXHtFiQsfCH2tTqS_PNg; _ibp=0:jpvw04bl:dbabe0b9-d286-4f13-b0bb-28f8f22f5054; nls2={%22a%22:0}; bfp_sn_rf_2a9d14d67e59728e1b5b2c86cb4ac6c4=Direct; jsonCookie_sw={\\"C5219\\":{\\"374_ISU\\":{\\"e\\":1,\\"p\\":0,\\"x\\":18065,\\"at1\\":1}}}; _litra_ses.1674=*; bfp_sn_rt_2a9d14d67e59728e1b5b2c86cb4ac6c4=1545276034635; bfp_sn_pl=1545276037_594332482364; brand=mywebmd; gtinfo={ \\"ct\\": \\"jiaozuo\\",\\"ctc\\": \\"14472\\",\\"c\\": \\"\\",\\"cc\\": \\"\\",\\"st\\": \\"ha\\",\\"sc\\": \\"35596\\",\\"z\\": \\"454150\\",\\"lat\\": \\"35.2429\\",\\"lon\\": \\"113.158\\",\\"dma\\": \\"156159\\",\\"cntr\\": \\"chn\\",\\"cntrc\\": \\"156\\",\\"ci\\": \\"59.48.93.142\\" }; refpath=; webmd_geoLoc=; mbox=PC#a4982a3228d547ac820f79fce93edc4e.22_27#1546485874|session#1765f46769cb4dd1be235ab0b32ecd25#1545278134|check#true#1545276334; _litra_id.1674=a-00xm--7346a15a-aa75-4264-b04f-af0ea3c58b42.1545266880.4.1545276274.1545273678.4f22ccae-49e2-475a-bb72-22c6a91e6fed; mnet_session_depth=1%7C1545276274117; ads_perm={%22segvar%22:%22segvarl_a318941xl_a319168xl_a319172xl_a319175xl_a319159xl_a319164xl_a319161xl_allxl_a319176xl_a319173xl_a319163xl_a319169xl_a319171x%22}; lpid=27c2ba054dbd5aba5667ab7d84a3c4c4; _ibs=0:jpw1lhmo:dd0149f7-5098-44b4-8559-ba2ade460652; AMCVS_16AD4362526701720A490D45%40AdobeOrg=1; AMCV_16AD4362526701720A490D45%40AdobeOrg=1099438348%7CMCIDTS%7C17886%7CMCMID%7C52886738189902804044191252348464931511%7CMCAAMLH-1545871677%7C11%7CMCAAMB-1545881077%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1545283477s%7CNONE%7CMCAID%7C2E0D736085311B5D-40000121E0011CC1%7CvVersion%7C2.1.0; s_cc=true; RT=\\"sl=1&ss=1545276028537&tt=22877&obo=0&sh=1545276295193%3D1%3A0%3A22877&dm=webmd.com&si=4232rwkv34&rl=1&ld=1545276295194\\"; ui={%22expmatch%22:1%2C%22aca_p1%22:1%2C%22vtime%22:25754607}; s_sq=%5B%5BB%5D%5D; sails.sid=s%3A5iqxBjprKUHCURUtFfD3s2EAZVFxA9wN.8ECJ4Ez0GCDItWw%2Bgs0lG8f4G6gTxMNZNkssQ%2BWPMnI; mf_933b121c-41c8-421c-87b4-252b0988801c=c9ab431753b856ffe288eb2302c76d64|12203469fa62cfe37ed047ab13bc95f6a36af9f8.1333831717.1545276274172,122033450ffae81cae281759869eccde35d86db6.-1544914791.1545276453247|1545276483639||2|||1|16.03',
                                    'client_id' => 'e4e3f73a-0ceb-4d37-939e-90ddb1238360',
                                    'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8',
                                    'accept-encoding' => 'gzip, deflate, br',
                                    'accept' => 'application/json'
                                ]
                            ]);
                            $html = (string)$res->getBody();
                            $json = json_decode($html,true);
                            $data= json_encode($json['data']);
                            file_put_contents($filename,$data);
                            sleep(1);
                        }catch(Exception\ClientException $e){
                            echo $e;
                        }
                    }
                }else{
                    if(in_array($val['name'],['neck','skin','general'])){
                        $filename = 'bodyJson'.DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR .$val['name'].".json";
                        $query['part'] = $val['id'];
                        try{
                            $res = $client->request('GET', $url, [
                                'verify' => false,
                                'query' => $query,
                                'headers' => [
                                    'postman-token' => '0e5363bf-4f43-5682-d0b7-c4b00bbf4250',
                                    'cache-control' => 'no-cache',
                                    'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                                    'timestamp' => 'Thu, 20 Dec 2018 03:28:05 GMT',
                                    'referer' => 'https://symptoms.webmd.com/default.htm?target=body',
                                    'if-none-match' => 'W/\\"4010-UcBlYABprNMWyip9M3AyMg\\"',
                                    'enc_data' => '6X68dnANX9yQVYVK2kWqLgOjlspHRm22wsTpF9eU4Rw=',
                                    'cookie' => '__cfduid=d16280eb3aec66e4bffcdec5d1346d8f01545266879; VisitorId=412d4002-3a4e-40f2-a472-f73707a81a11; cmt=; ck_consent=true; s_vi=[CS]v1|2E0D736085311B5D-40000121E0011CC1[CE]; gdpreu=eu%7CCHN%7CNOTIN%7C1545266880; mf_user=5693dfb83bf080c0e29d4c2a45f4fd56|; turn=2500805827616571407|181220|y; __gads=ID=8dbb5eb2820cc9f6:T=1545266883:S=ALNI_MZKi-3ou8uXHtFiQsfCH2tTqS_PNg; _ibp=0:jpvw04bl:dbabe0b9-d286-4f13-b0bb-28f8f22f5054; nls2={%22a%22:0}; bfp_sn_rf_2a9d14d67e59728e1b5b2c86cb4ac6c4=Direct; jsonCookie_sw={\\"C5219\\":{\\"374_ISU\\":{\\"e\\":1,\\"p\\":0,\\"x\\":18065,\\"at1\\":1}}}; _litra_ses.1674=*; bfp_sn_rt_2a9d14d67e59728e1b5b2c86cb4ac6c4=1545276034635; bfp_sn_pl=1545276037_594332482364; brand=mywebmd; gtinfo={ \\"ct\\": \\"jiaozuo\\",\\"ctc\\": \\"14472\\",\\"c\\": \\"\\",\\"cc\\": \\"\\",\\"st\\": \\"ha\\",\\"sc\\": \\"35596\\",\\"z\\": \\"454150\\",\\"lat\\": \\"35.2429\\",\\"lon\\": \\"113.158\\",\\"dma\\": \\"156159\\",\\"cntr\\": \\"chn\\",\\"cntrc\\": \\"156\\",\\"ci\\": \\"59.48.93.142\\" }; refpath=; webmd_geoLoc=; mbox=PC#a4982a3228d547ac820f79fce93edc4e.22_27#1546485874|session#1765f46769cb4dd1be235ab0b32ecd25#1545278134|check#true#1545276334; _litra_id.1674=a-00xm--7346a15a-aa75-4264-b04f-af0ea3c58b42.1545266880.4.1545276274.1545273678.4f22ccae-49e2-475a-bb72-22c6a91e6fed; mnet_session_depth=1%7C1545276274117; ads_perm={%22segvar%22:%22segvarl_a318941xl_a319168xl_a319172xl_a319175xl_a319159xl_a319164xl_a319161xl_allxl_a319176xl_a319173xl_a319163xl_a319169xl_a319171x%22}; lpid=27c2ba054dbd5aba5667ab7d84a3c4c4; _ibs=0:jpw1lhmo:dd0149f7-5098-44b4-8559-ba2ade460652; AMCVS_16AD4362526701720A490D45%40AdobeOrg=1; AMCV_16AD4362526701720A490D45%40AdobeOrg=1099438348%7CMCIDTS%7C17886%7CMCMID%7C52886738189902804044191252348464931511%7CMCAAMLH-1545871677%7C11%7CMCAAMB-1545881077%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1545283477s%7CNONE%7CMCAID%7C2E0D736085311B5D-40000121E0011CC1%7CvVersion%7C2.1.0; s_cc=true; RT=\\"sl=1&ss=1545276028537&tt=22877&obo=0&sh=1545276295193%3D1%3A0%3A22877&dm=webmd.com&si=4232rwkv34&rl=1&ld=1545276295194\\"; ui={%22expmatch%22:1%2C%22aca_p1%22:1%2C%22vtime%22:25754607}; s_sq=%5B%5BB%5D%5D; sails.sid=s%3A5iqxBjprKUHCURUtFfD3s2EAZVFxA9wN.8ECJ4Ez0GCDItWw%2Bgs0lG8f4G6gTxMNZNkssQ%2BWPMnI; mf_933b121c-41c8-421c-87b4-252b0988801c=c9ab431753b856ffe288eb2302c76d64|12203469fa62cfe37ed047ab13bc95f6a36af9f8.1333831717.1545276274172,122033450ffae81cae281759869eccde35d86db6.-1544914791.1545276453247|1545276483639||2|||1|16.03',
                                    'client_id' => 'e4e3f73a-0ceb-4d37-939e-90ddb1238360',
                                    'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8',
                                    'accept-encoding' => 'gzip, deflate, br',
                                    'accept' => 'application/json'
                                ]
                            ]);
                            $html = (string)$res->getBody();
                            $json = json_decode($html,true);
                            $data= json_encode($json['data']);
                            file_put_contents($filename,$data);
                            sleep(1);
                        }catch(Exception\ClientException $e){
                            echo $e;
                        }
                    }
                }
            }
        }
    }

}



