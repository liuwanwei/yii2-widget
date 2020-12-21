<?php 

namespace buddysoft\widget\helpers;

use Yii;
use yii\rbac\Item;
use mdm\admin\models\searchs\AuthItem as AuthItemSearch;
use mdm\admin\components\Configs;
use mdm\admin\models\Assignment;

class RoleHelper{

	/**
	 *
	 * 获取某个用户的角色
	 *
	 */
	
	public static function roleForUser($userId){
		$manager = Yii::$app->getAuthManager();

		$model = new Assignment($userId);
		$items = $model->getItems();
		$assigned = $items['assigned'];		
		foreach ($assigned as $name => $value) {
			$item = $manager->getRole($name);
			if (! empty($item)) {
				// 返回用户的第一个角色
				return $name;
			}
		}

		return '未找到';
	}

	/**
	 * 获取后台已经创建的所有角色
	 */	
	public static function roles(){
		$searchModel = new AuthItemSearch(['type' => Item::TYPE_ROLE]);
		$dataProvider = $searchModel->search(null);
		return $dataProvider->getModels();
	}


	/**
	 * 获取可用作下拉框选项的可用角色列表
	 */
	public static function getAvailableRoleOptions(){
        $roles = static::roles();

        $items = [];
        foreach ($roles as $model) {
            $items[$model->name] = $model->name;
        }

        return $items;
    }

	/**
	 *
	 * 赋予用户某个角色
	 * @param string $newRoleName 新角色名字
	 * @param int $userId 用户 id
	 * 
	 * 先删除旧角色，再添加新角色，支持清空用户旧角色功能
	 */
	
	public static function assignRole($newRoleName, $userId){		
		$model = new Assignment($userId);
		$manager = Yii::$app->getAuthManager();

		// 删除所有已有旧角色
		$items = $model->getItems();
		$assigned = $items['assigned'];
		foreach ($assigned as $name => $value) {
			$item = $manager->getRole($name);
			if (! empty($item)) {
				$manager->revoke($item, $userId);
			}else{
				throw new Exception('用户权限里混杂了非角色部分: ' . $name, 1);
			}
		}

		// 赋予新角色
		if (! empty($newRoleName)) {
			$roleItem = $manager->getRole($newRoleName);
			$manager->assign($roleItem, $userId);
			
         	$config = Configs::instance();
         	$cache = $config->cache;
        	$cache->flush();
		}

	}
	/**
	 * 移除后台用户的角色
	 * @param $userId 用户Id
	 * 
	 */
	public static function removeRole($userId){
		$model = new Assignment($userId);
		$manager = Yii::$app->getAuthManager();

		// 删除所有已有旧角色
		$items = $model->getItems();

		$assigned = $items['assigned'];
		foreach ($assigned as $name => $value) {
			$item = $manager->getRole($name);
			if (! empty($item)) {
				$manager->revoke($item, $userId);
			}else{
				throw new Exception('用户后台身份里混杂了非角色部分: ' . $name, 1);
			}
		}
	}	
}

 ?>