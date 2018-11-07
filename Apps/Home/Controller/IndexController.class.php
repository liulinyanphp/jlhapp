<?php
namespace Home\Controller;
use Think\Controller;
use Common\Service\UtilService;
use Common\Service\RsaService;
use Common\Service\Bitcoin;
use Common\Service\OmniService;
use Common\Util\CommonLogs;
use Common\Service\AddressService;

//极光推送相关类引入
Vendor("JPush.autoload");
use JPush\Client as JPushClient;
use JPush\Exceptions as JPushExceptions;

class IndexController extends Controller {


    public function jpushMsgAction()
    {
        layout(false);
        $client =  new JPushClient(C('JPUSH.PUSH_KEY'),C('JPUSH.PUSH_SECRET'));
        $push_payload = $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert('hi lly');
        try {
            $response = $push_payload->send();
            print_r($response);
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        } catch (JPushExceptions\APIRequestException $e) {
            $error_data['status'] = $e->getCode();
            $error_data['message'] = $e->getMessage();
        }
        print_r($error_data);
    }

    public function indexAction()
    {
    	layout(false);
    	echo 'Hi Yoko';
    	die();
    	echo '欢迎您的到来';

    	$usdt_in = array('1444.45834');
    	$usdt_out =  array('1456','1416','1574','1926','2132','2335','1178');
    	$in_usdt_sum = array_sum($usdt_in);
        $out_usdt_sum = array_sum($usdt_out);
    	echo $out_usdt_sum-$in_usdt_sum;
    	echo '<br/>';

    	$eth_in = array('3','14.82265353','5.4992');
    	$eth_out = array('4');
        $in_eth_sum = array_sum($eth_in);
    	$out_eth_sum = array_sum($eth_out);

    	echo $out_eth_sum-$in_eth_sum.'<br/>';

        $usdt_in2 = array(0);
        $usdt_out2 =  array('841','1625','1621','1853','715.5','1091','1111');
        $in_usdt_sum2 = array_sum($usdt_in2);
        $out_usdt_sum2 = array_sum($usdt_out2);
        echo $out_usdt_sum2-$in_usdt_sum2;
        echo '<br/>';

        $eth_in2 = array('2.06','9.485','9.845');
        $eth_out2 = array('0');
        $in_eth_sum2 = array_sum($eth_in2);
        $out_eth_sum2 = array_sum($eth_out2);

        echo $out_eth_sum2-$in_eth_sum2.'<br/>';

        echo ($out_eth_sum2+$out_eth_sum)-($in_eth_sum2+$in_eth_sum);

    }

    public function drawImgAction()
    {
        Vendor('Jpgraph.jpgraph');

        Vendor('Jpgraph.jpgraph_pie');

        // Some data
        $data = array(40,21,17,14,23);

        // Create the Pie Graph.
        $graph = new \PieGraph(350,250);

        $theme_class="DefaultTheme";
        //$graph->SetTheme(new $theme_class());

        // Set A title for the plot
        //$graph->title->Set("A Simple Pie Plot");
        $graph->SetBox(true);

        // Create
        $p1 = new \PiePlot($data);
        $graph->Add($p1);

        $p1->ShowBorder();
        $p1->SetColor('black');
        $p1->SetSliceColors(array('#1E90FF','#2E8B57','#ADFF2F','#DC143C','#BA55D3'));
        $filename = ROOT.'/Public/upload/Jpgraph/b.png';
        //$graph->Stroke("/tmp/jpgraph/a.png");
        $graph->Stroke($filename);



//        $data1y=array(-8,8,9,3,5,6);
//        $data2y=array(18,2,1,7,5,4);
//
//        // Create the graph. These two calls are always required
//        $graph = new \Graph(500,400);
//        $graph->cleartheme();
//        $graph->SetScale("textlin");
//
//        $graph->SetShadow();
//        $graph->img->SetMargin(40,30,20,40);
//
//        Vendor('Jpgraph.jpgraph_bar.php');
//        // Create the bar plots
//
//        $b1plot = new \BarPlot($data1y);
//        $b1plot->SetFillColor("orange");
//        $b1plot->value->Show();
//
//        $b2plot = new \BarPlot($data2y);
//        $b2plot->SetFillColor("blue");
//        $b2plot->value->Show();
//
//        // Create the grouped bar plot
//        $gbplot = new \AccBarPlot(array($b1plot,$b2plot));
//
//        // ...and add it to the graPH
//        $graph->Add($gbplot);
//
//        $graph->title->Set("Accumulated bar plots");
//        $graph->xaxis->title->Set("X-title");
//        $graph->yaxis->title->Set("Y-title");
//
//        $graph->title->SetFont(FF_FONT1,FS_BOLD);
//        $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
//        $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
//
//        // Display the graph
//        $graph->Stroke();
//        echo 'aaa';
    }



    public  function adrAction()
    {
        $param = array(
            'plat_code'=>'HXYL',
            'currency_code'=>'usdt',
            'num' => 2,
            'created_by'=> 'sys'
        );
        $res['status'] = 0;
        $res['message'] = 'ok';
        $array_key = array('plat_code','currency_code','num','created_by');
        $param_key = array_keys($param);
        if(!empty(array_diff($array_key,$param_key)))
        {
            $res['status'] = '-1';
            $res['message'] = '参数出错啦';
            $this->ajaxReturn($res);
            exit();
        }
        $plat_code = strtoupper($param['plat_code']);
        $num = $param['num'];

        $insert_data['plat_code'] = $plat_code;
        $insert_data['currency_code'] = $param['currency_code'];
        $insert_data['created_by'] = $param['created_by'];

        //对密钥进行加密
        $address_key = C("ADDRESS_KEY.$plat_code");
        $public_key = ROOT.$address_key['ADDRESS_PUBKEY'];
        //公钥加密
        $bitcoin = OmniService::getInstance();
        $rsa = new RsaService();
        $res['status'] = 0;
        $str = '';
        $addressM = D('TAddress');
        for($i=1;$i<=$num;$i++)
        {
            $tmpdata = $insert_data;
            $address = $bitcoin->getnewaddress();
            //$address = '1FfN2xixPtiWjQk1pJbXiJwUUsGHEcySxP';
            if($address['res_info'] != 'ok')
            {
                $str .= "第".$i."个地址创建失败; ";
                continue;
            }
            $address = $address['result_rows'];
            //密钥
            $keyv = 'cszb'.$bitcoin->dumpprivkey($address);
            if( $keyv == 'cszb')
            {
                $str .="第".$i."个地址获取密钥失败; ";
                continue;
            }
            $encrypt = $rsa->rsaEncrypt($keyv,$public_key);
            $tmpdata['address'] = $address;
            $tmpdata['privkey'] = $encrypt;
            $addressM->data($tmpdata)->add();
        }
        if(!empty($str))
        {
            $res['status'] = 1;
            $res['message'] = $str;
        }
        $this->ajaxReturn($res);
    }
}