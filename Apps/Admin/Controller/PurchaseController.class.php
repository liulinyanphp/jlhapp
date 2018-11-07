<?php
namespace Admin\Controller;

use Common\Conf\BaseConfig;
use Org\Util\Result;
use Common\Service\AddressService;
use Common\Utils\CommonUtils;
use \Org\Util\Exception;
use Common\Service\PurchaseStockService;

class PurchaseController extends AdminBaseController {

    /**
     * 条件查询
     */
    public function listAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $where['id'] = array('gt',0);
        if(I('purchaseId')) {
            $where['purchase_id'] = array('eq',trim(I('purchaseId')));
        }
        if(I('receiveAddr')){
            $where['receive_addr'] = array('like','%'.I('receiveAddr').'%');
        }
        if(I('currency_code')){
            $where['currencyName'] = array('eq',I('currencyName'));
        }
        if(I('purchaseType') && I('purchaseType') != BaseConfig::PurchaseTypeEmpty){
            $where['purchase_type'] = I('purchaseType');
        }
        $stockM = M('TPurchaseStock');
        $field = 'purchase_id,currency_code,purchase_num,remain_num, curr_price,operator_name,purchase_type,
        sender_addr,receive_addr,total_amount,trade_no
        created_by,created_date,last_modified_by,last_modified_date';
        $count = $stockM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $stockM->field($field)->where($where)->page($pageNow .','. $pageSize)->order('id desc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('stockList',$data);
        $this->assign('PurchaseTypeSearch',BaseConfig::$PurchaseTypeSearch);
        $this->assign('param',I('get.'));
        $this->display();
    }

    /**
     * 条件查询
     */
    public function logListAction()
    {
        $pageNow = I('p',1);
        $pageSize = 10;
        $purchase_id = I('purchase_id');
        if(empty($purchase_id))
        {
            $this->redirect('list');eixt;
        }
        $where['purchase_id'] = array('eq',$purchase_id);

        $balanceLogM = M('TPurchaseStockLog');
        $count = $balanceLogM->where($where)->count();
        $pageNow = ( $pageNow >$count )  ? $count : ( $pageNow < 1 ? 1: $pageNow );
        $data = $balanceLogM->where($where)->page($pageNow .','. $pageSize)->order('id asc')->select();
        $Page = new  \Think\AdminPage ( $count, $pageSize, '' );
        $show = $Page->show (); // 分页显示输出
        $this->assign('page',$show);
        $this->assign('stockLogList',$data);
        $this->assign('PurchaseTypeSearch',BaseConfig::$PurchaseTypeSearch);
        $this->display('logList');
    }

    /**
     * 添加
     */
    public function addAction()
    {
        if(IS_POST)
        {
           try{
                $data = I('post.');
                foreach($data as $key=>$obj)
                {
                    $v = trim($obj);
                    if($v=='')
                    {
                        $conf_error_key = strtoupper('error_'.$key.'_is_empty');
                        Exception::throwsErrorMsg(C($conf_error_key));
                        break;
                    }
                    //检查数字类型
                    $conf_error_ck_type = strtoupper('error_'.$key.'_need_number');
                    $conf_info = C('ADMIN_NUMBER_CHECK');
                    if(!isset($conf_info[$conf_error_ck_type])){
                        continue;
                    }else{
                        $fun_name = $conf_info[$conf_error_ck_type][2];
                        if(!CommonUtils::$fun_name($v)){
                            Exception::throwsErrorMsg($conf_info[$conf_error_ck_type]);
                        }
                    }
                }
                $data['operator'] = session("username");
                $purchaseService = new PurchaseStockService();
                $result = $purchaseService->purchase_create($data);
                if($result['res_info'] =='ok')
                {
                    $result = Result::innerResultSuccess();
                    $result['res_info'] = C('PURCHASE_ADD_OK');
                    $result['result_rows'] = U('list');
                }else{
                    //报错了
                    //Exception::throwsErrorMsg(C('ERROR_PURCAHSE_STOCK_ADD_ERR'));
                    $err = array($result['result'],$result['res_info']);
                    Exception::throwsErrorMsg($err);
                }
           } catch(\Exception $e){
               $result =  Result::innerResultFail($e);
           }
           $this->ajaxReturn($result);
           return '';
        }else {
            //获取公司运营地址
            $bus_address = AddressService::get_bus_address();
            //获取公司后台配置的币种编码
            $currencyD = D('Currency');
            $currency_code = $currencyD->get_currency_code();
            $this->assign('currency_code', $currency_code);
            $this->assign('bus_address', $bus_address);
            $this->display();
        }
    }
}