<?php
namespace Admin\Model;
use Think\Model;
class ConfigModel extends Model
{
    public $tables = array(
        'keywordslist'=>'t_keywords_config',
        'keywordsadd' =>'t_keywords_config',
        'keywordsedit'=>'t_keywords_config',

        'crowdunitlist'=>'t_crowd_unit_config',
        'crowdunitadd'=>'t_crowd_unit_config',
        'crowdunitedit'=>'t_crowd_unit_config',

        'ratinglist'=>'t_rating_config',
        'ratingadd'=>'t_rating_config',
        'ratingedit'=>'t_rating_config',

        'tokenlist'=>'t_token_mapping_config',
        'tokenadd'=>'t_token_mapping_config',
        'tokenedit'=>'t_token_mapping_config',

        'pushcfglist'=>'t_push_config',
        'pushcfgadd'=>'t_push_config',
        'pushcfgedit'=>'t_push_config',

        'investmentlist'=>'t_investment_ins',
        'investmentadd'=>'t_investment_ins',
        'investmentedit'=>'t_investment_ins',

        'channellist'=>'t_public_channel',
        'channeladd'=>'t_public_channel',
        'channeledit'=>'t_public_channel',

        'competitorlist'=>'t_competitor_config',
        'competitoradd'=>'t_competitor_config',
        'competitoredit'=>'t_competitor_config',

        'analysislist'=>'t_analysis_config',
        'analysisadd'=>'t_analysis_config',
        'analysisedit'=>'t_analysis_config',

        'helplist'=>'t_help_info',
        'helpadd'=>'t_help_info',
        'helpedit'=>'t_help_info'
    );

    public $validates = array(
        'keywordsadd' => array(array('name','require','关键词名称不能为空!')),
        'keywordsedit' => array(array('name','require','关键词名称不能为空!')),

        'crowdunitadd'=>array(
            array('code','require','众筹单位编码不能为空!',1),
            array('code','','众筹单位编码不得重复!',1,unique),
            array('name','require','众筹单位名称不能为空!',1),
            array('name','','众筹单位名称不得重复!',1,unique),
            array('property_id','require','众筹单位标示id不能为空!',1),
            array('property_id','number','众筹单位标示id只能为数字',1)
        ),

        'ratingadd'=> array(array('name','require','评级级别名称不能为空！')),
        'ratingedit'=>array(array('name','require','评级级别名称不能为空！')),

        'tokenadd'=>array(
            array('name','require','token分配名称不能为空!',1),
            array('txtcode','require','token分配值不能为空!',1),
            array('txtcode','number','token分配值只能为数字',1)
        ),
        'tokenedit'=>array(
            array('name','require','token分配名称不能为空!',1),
            array('txtcode','require','token分配值不能为空!',1),
            array('txtcode','number','token分配值只能为数字',1)
        ),
        'pushcfgadd'=>array(
            array('title','require','推送的标题不能为空!',1),
            array('summary','require','推送的简介内容不能为空!',1)
        ),
        'investmentadd' => array(
            array('name','require','投资机构名称不能为空!',1),
            array('logo_img_url','require','投资机构logo不能为空!',1)
        ),
        'investmentedit' => array(
            array('name','require','投资机构名称不能为空!',1),
            array('logo_img_url','require','投资机构logo不能为空!',1)
        ),
        'channeladd'=>array(
            array('name','require','渠道名称不能为空!',1),
            array('logo_img_url','require','渠道logo不能为空!',1)
        ),
        'channeledit'=>array(
            array('name','require','渠道名称不能为空!',1),
            array('logo_img_url','require','渠道logo不能为空!',1)
        ),
        'competitoradd' => array(
            array('name','require','渠道名称不能为空!',1),
            array('logo_img_url','require','渠道logo不能为空!',1)
        ),
        'competitoredit' => array(
            array('name','require','渠道名称不能为空!',1),
            array('logo_img_url','require','渠道logo不能为空!',1)
        ),
        'analysisadd' => array(
            array('title','require','行业分析名称不能为空!',1),
            array('content','require','行业分析内容不能为空!',1)
        ),
        'analysisedit' => array(
            array('title','require','行业分析名称不能为空!',1),
            array('content','require','行业分析内容不能为空!',1)
        ),
        'helpsadd' => array(
            array('title','require','新手帮助标题不能为空!',1),
            array('content','require','新手帮助内容不能为空!',1)
        ),
        'helpedit' => array(
            array('title','require','新手帮助标题不能为空!',1),
            array('content','require','新手帮助内容不能为空!',1)
        )
    );

    public function __construct($name)
    {
        //暂时解决字段缓存问题
        unlink(RUNTIME_PATH.'/Data/_fields/token_one.config.php');
        $this->tableName = $this->tables[strtolower(ACTION_NAME)];
        $this->_validate = $this->validates[strtolower(ACTION_NAME)];
        parent::__construct($name);

    }

//	protected $_validate = array(
//		array('name','require','项目名称不能为空!',1)
//	);

	public function getlist($where=array(),$pageNow='1',$limitRows='10')
	{
		return $this->where($where)->order('created_date desc')->page($pageNow .','. $limitRows)->select();
	}
	
	public function getcount($w=array())
    {
        if (empty($w)) {
            return $this->count();
        } else {
            return $this->where($w)->count();
        }
    }
}
?>