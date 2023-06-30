<?php


namespace Qz\Admin\Permission\Models;


class AdminRoleRequest extends Model
{
    protected $fillable = [
        'admin_role_id',
        'admin_request_id',
        'type'
    ];

    const CHARACTER = '+';
    const SELF = 'SELF'; //自己
    const THIS = 'THIS'; //本部门
    const PEER = 'PEER'; //同级部门
    const CHILDREN = 'CHILDREN'; //下级部门
    const UNDEFINED = 'UNDEFINED'; //其他

    const TYPE_SELF = self::SELF;
    const TYPE_THIS = self::THIS;
    const TYPE_PEER = self::PEER;
    const TYPE_CHILDREN = self::CHILDREN;
    const TYPE_UNDEFINED = self::UNDEFINED;

//    const TYPE_PEER_CHILDREN = self::PEER . self::CHARACTER . self::CHILDREN;
//    const TYPE_SELF_PEER = self::SELF . self::CHARACTER . self::PEER;
//    const TYPE_SELF_CHILDREN = self::SELF . self::CHARACTER . self::CHILDREN;
//
//    const TYPE_DESC = [
//        self::TYPE_SELF => "只看自己",
//        self::TYPE_CHILDREN => "只看下级部门成员数据",
//        self::TYPE_PEER => "只看同级部门成员数据",
//        self::TYPE_PEER_CHILDREN => "只看同级及下级部门成员数据",
//        self::TYPE_SELF_PEER => "只看自己及同级部门成员数据",
//        self::TYPE_SELF_CHILDREN => "只看自己及下级部门成员数据",
//        self::TYPE_UNDEFINED => "只看未知数据",
//    ];

    const TYPE_BASE_DESC = [
        self::SELF => "自己",
        self::THIS => "本部门",
        self::PEER => "同级部门",
        self::CHILDREN => "下级部门",
        self::UNDEFINED => "其他",
    ];

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function adminRequest()
    {
        return $this->belongsTo(AdminRequest::class);
    }
}
