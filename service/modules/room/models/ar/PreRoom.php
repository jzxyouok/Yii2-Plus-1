<?php

namespace service\modules\room\models\ar;

use Yii;
use service\modules\common\services\CommonService;
use service\modules\common\models\ar\IdAlloc;

/**
 * Class PreRoom
 * 预备房间
 * @package service\models
 */
class PreRoom extends \yii\base\Model
{


    public static function getDb()
    {
        return \Yii::$app->redis;  // 使用名为 "db" 的应用组件
    }
    const REDIS_KEY_PRE_ROOM= 'pre_room_info';
    const REDIS_KEY_USER_PRE_ROOM = 'user_pre_room';

   
    /**
     * 新增或修改预备房间信息
     * @param array 
     * @return string 
     */
    public static function setPreRoomInfo($arrPreRoomInfo) {
        $redis = self::getDb();
        $intPreRoomId = $arrPreRoomInfo['pre_room_id'];
        if (empty($intPreRoomId)) {
            $client = CommonService::serviceClient("/common/id-alloc", 'php');
            $intPreRoomId = $client->allocId(IdAlloc::PRE_ROOM_ID_ALLOC_KEY);
            $arrPreRoomInfo['pre_room_id'] = $intPreRoomId;
        }
        Yii::warning('intPreRoomId: ' . $intPreRoomId);
        $redis->HSET(self::REDIS_KEY_PRE_ROOM , $intPreRoomId, serialize($arrPreRoomInfo));
        return $arrPreRoomInfo;
        
    }

    /**
     * 获取房间信息
     * @return string 
     */
    public static function getPreRoomInfo($intPreRoomId) {
        $redis = self::getDb();
        $ret = $redis->HGET(self::REDIS_KEY_PRE_ROOM, $intPreRoomId);
        $ret = unserialize($ret);
        return  $ret;
    }

    /**
     * 获取房间信息
     * @return string 
     */
    public static function getBatchPreRoomInfo($arrPreRoomIds) {
        $redis = self::getDb();
        $arrRedisParam = [];
        $arrRedisParam[] = self::REDIS_KEY_PRE_ROOM;
        $arrRedisParam = array_merge($arrRedisParam, $arrPreRoomIds);
        //$ret = $redis->HMGET(self::REDIS_KEY_USER_PRE_ROOM , $arrRedisParam);
        $ret = $redis->executeCommand('hmget', $arrRedisParam);
        $arrRet = [];
        foreach($ret as $item) {
            $arr = unserialize($item);
            $arrRet[] = $arr;
        }
        return  $arrRet;
    }

    /**
     * 删除房间信息
     * @param array arrPreRoomInfo 房间从Redis中读取到的信息
     * @return string 
     */
    public static function rmPreRoomInfo($intPreRoomId) {
        $redis = self::getDb();
        $ret = $redis->HDEL(self::REDIS_KEY_PRE_ROOM, $intPreRoomId);
        return  $ret;
    }

    /**
     * 新增或修改用户所在预备房间信息
     * @param array 
     * @return string 
     */
    public static function setBatchUserPreRoomId($arrUserIds, $intPreRoomId) {
        $redis = self::getDb();
        $arrRedisParam = [];
        $arrRedisParam[] = self::REDIS_KEY_USER_PRE_ROOM;
        foreach($arrUserIds as $id) {
            $arrRedisParam[] = "$id";
            $arrRedisParam[] = "$intPreRoomId";
        }
        // $ret = $redis->HMSET(self::REDIS_KEY_USER_PRE_ROOM , $arrRedisParam);
        $ret = $redis->executeCommand('hmset', $arrRedisParam);
        return $ret;
        
    }

     /**
     * 获取用户所在预备房间信息
     * @param array 
     * @return string 
     */
    public static function getBatchUserPreRoomId($arrUserIds) {
        $redis = self::getDb();
        $arrRedisParam = [];
        $arrRedisParam[] = self::REDIS_KEY_USER_PRE_ROOM;
        foreach($arrUserIds as $id) {
            $arrRedisParam[] = $id;
        }
        //$ret = $redis->HMGET(self::REDIS_KEY_USER_PRE_ROOM , $arrRedisParam);
        $ret = $redis->executeCommand('hmget', $arrRedisParam);

        return $ret;
        
    }

    /**
     * 删除用户所在预备房间信息
     * @param array 
     * @return string 
     */
    public static function rmBatchUserPreRoomId($arrUserIds) {
        $redis = self::getDb();
        $arrRedisParam = [];
        $arrRedisParam[] = self::REDIS_KEY_USER_PRE_ROOM;

        $arrRedisParam = array_merge($arrRedisParam, $arrUserIds);
        $ret = $redis->executeCommand('hdel', $arrRedisParam);
        return $ret;

    }
   
}