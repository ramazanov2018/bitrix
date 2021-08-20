<?php 
namespace Serv;
class UserRole{
	static $GroupIdRoleDirection = 20;// Руководитель направления
	
	/* Руководитель направления
	 * функция проверяет принадлежность пользователя к группе. 
	 *	возвращает true - Если пользователь cостоит в группе и false - если нет
	 */
	public function UserIsRoleDirection($UserId = 0){
		if($UserId <= 0){
			global $USER; 
			$UserId = $USER->GetID();			
		}
		
		$UserGroup = \CUser::GetUserGroup($UserId);
		return in_array(self::$GroupIdRoleDirection, $UserGroup) !== false;
	}
}

?>