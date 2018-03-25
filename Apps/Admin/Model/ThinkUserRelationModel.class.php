<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class ThinkUserRelationModel extends RelationModel{
    //定义主表名称
    protected $tableName = 'think_user';
    //定义关联关系
    protected $_link = array(
            'role'=>array(
            'mapping_type'=>self::MANY_TO_MANY,
            'foreign_key'=>'user_id',//指定主表外键
            'relation_foreign_key'=>'role_id',//指定关联表外键
            'relation_table'=>'think_role_user',//指定中间表名称
            'mapping_fields'=>'id,name,remark'//读取的字段
        ),
    );
}
?>