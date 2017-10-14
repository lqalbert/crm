<?php
namespace Home\Controller;


class CounselArticleController extends CommonController {

    protected $table = "CounselArticle";


    public function index(){
        $this->assign('types', $this->M->getType());
        $this->display();
    }

    //给发布人的
    public function _before_add(){
        $_POST['creator_id'] = session('uid');
        $_POST['creator'] = session('account')['userInfo']['realname'];
    }

    /**
     * 公用 设置参数
     * 子类
     * @return  null
     * 
     **/
    public function setQeuryCondition() {
        $title = I("get.name");
        if ($title) {
            $this->M->where(array('title'=>array('LIKE', "%".$title."%")));
        }
        $this->M->where(array('creator_id'=> session('uid')));
    }

    /**
     * 公用 获取列表
     *
     * @return array() || null
     * 
     **/
    public function getList(){

        $result = $this->_getList();
        $types = $this->M->getType();
        
        $list = &$result['list'];
        foreach ($list as &$item) {
            $item['type_text'] = $types[$item['type']];
        }


        if (IS_AJAX) {
            $this->ajaxReturn($result);
            // $this->ajaxReturn($this->M->getLastSql());
        }  else {
            
            return $result;
        }

    }

}