-- 用户积分交易
DROP PROCEDURE IF EXISTS pro_trans_user_point;

DELIMITER //
CREATE PROCEDURE pro_trans_user_point(
	-- IN to_cmpid VARCHAR(50),       #company id
	IN to_userid VARCHAR(50),		#to userid		
	IN trans_type CHAR(1),			#transaction type:0：转入；1：转出；2：获得；3：扣除
	IN trans_point DECIMAL(10,2),			#transaction point
	IN point_rule_id VARCHAR(50),		#point rule id
	IN get_from VARCHAR(50),		#get from 
	IN get_from_id VARCHAR(50),	
	IN reason VARCHAR(500),			#reason or remark  当值为{user.real_name}时，会从user表中取real_name
  OUT result TINYINT,  #当值为{user.real_name}时，会从user表中取real_name
  OUT message VARCHAR(200),
	OUT cur_available_point DECIMAL(10,2)
)
LABEL_PRO:BEGIN


	DECLARE EXIT HANDLER FOR SQLEXCEPTION -- SQL任何异常，从这退出
	BEGIN 
		ROLLBACK; 
		SET @IsError = 1;
		SET result = 0;
		SET message = 'Transaction Error';
	END; 

  SET @temp=NULL;
  SET @str_pos=0;

	SET @to_cmpid=NULL;
	SET @to_user_real_name=NULL;
	SET @from_cmpid=NULL;
	SET @from_user_real_name=NULL;
  SET @IsError = 0;
	SET @update_count=0;
	SET @real_update_count=1;
	SET @from_trans_type=''; 	#源交易类型
	SET @cur_available_point=0;


#参数效验
IF trans_point IS NULL OR trans_point < 0 THEN
	SET result = 11;
	SET message = 'trans_point is not available';
	SELECT result,message,cur_available_point;
	LEAVE LABEL_PRO;
END IF;


#将''和'NULL'转为NULL
IF point_rule_id='NULL' OR point_rule_id=''  THEN
	SET point_rule_id = NULL;
END IF;

#自己不能转给自己
IF get_from='user' AND to_userid=get_from_id THEN
	SET result = 21;
	SET message = 'you can not transfer points to yourself';
	SELECT result,message,cur_available_point;
	LEAVE LABEL_PRO;
END IF;


#得到对应企业id和用户名(select赋值不能多个变量，只有先合并再分割)
-- SELECT @to_cmpid:=company_id,@to_user_real_name=real_name FROM eln_fw_user WHERE kid=to_userid AND is_deleted='0';
SELECT CONCAT_WS(',',company_id,real_name) INTO @temp FROM eln_fw_user WHERE kid=to_userid AND is_deleted='0';

set @str_pos=LOCATE(',',@temp);
SET @to_cmpid=SUBSTRING(@temp ,1,@str_pos-1);
SET @to_user_real_name=SUBSTRING(@temp ,@str_pos+1);

IF @temp IS NULL OR @to_cmpid=''  THEN
	SET result = 31;
	SET message = 'your company id is null';
	SELECT result,message,cur_available_point;
	LEAVE LABEL_PRO;
END IF;

SET @temp=NULL; -- 清空临时变量


#源是user，则双向
IF get_from='user' THEN
-- SELECT @from_cmpid:=company_id,@from_user_real_name=real_name FROM eln_fw_user WHERE kid=get_from_id AND is_deleted='0';
SELECT CONCAT_WS(',',company_id,real_name) INTO @temp FROM eln_fw_user WHERE kid=get_from_id AND is_deleted='0';

set @str_pos=LOCATE(',',@temp);
SET @from_cmpid=SUBSTRING(@temp ,1,@str_pos-1);
SET @from_user_real_name=SUBSTRING(@temp ,@str_pos+1);

	IF @temp IS NULL OR @from_cmpid=''  THEN
		SET result = 32;
		SET message = 'the company id of your trading partner is null';
	  SELECT result,message,cur_available_point;
		LEAVE LABEL_PRO;
	END IF;
	SET @temp=NULL; -- 清空临时变量
END IF;


-- 汇总表不存在则加入
IF !EXISTS(SELECT * FROM eln_fw_user_point_summary WHERE user_id=to_userid) then
		INSERT INTO `eln_fw_user_point_summary` (`kid`, `user_id`, `growth_system_id`, `company_id`, `available_point`, `get_point`, `transfer_in_point`, `transfer_out_point`, `version`, `created_by`, `created_at`, `created_from`, `created_ip`,`is_deleted`) 
		VALUES (UPPER(UUID()), to_userid, '00000000-0000-0000-0000-000000000001', @to_cmpid, '0.00', '0.00', '0.00', '0.00', '1', 'Procedure', '20160307', 'Procedure', '127.0.0.1', '0') ;
END IF;

#源是user，则双向
IF get_from='user' THEN
	IF !EXISTS(SELECT * FROM eln_fw_user_point_summary WHERE user_id=get_from_id) then
		INSERT INTO `eln_fw_user_point_summary` (`kid`, `user_id`, `growth_system_id`, `company_id`, `available_point`, `get_point`, `transfer_in_point`, `transfer_out_point`, `version`, `created_by`, `created_at`, `created_from`, `created_ip`,`is_deleted`) 
		VALUES (UPPER(UUID()), get_from_id, '00000000-0000-0000-0000-000000000001', @from_cmpid, '0.00', '0.00', '0.00', '0.00', '1', 'Procedure', '20160307', 'Procedure', '127.0.0.1', '0') ;
	END IF;
END IF;



#积分不够不能转出
SELECT available_point INTO @cur_available_point FROM eln_fw_user_point_summary WHERE user_id=to_userid AND is_deleted='0';
IF trans_type='1' THEN #转出
	IF @cur_available_point<trans_point THEN
		SET result = 41;
		SET Message = 'your available point is not enough';
	  SELECT result,message,cur_available_point;
		LEAVE LABEL_PRO;
	END IF;
END IF;




-- 开始事务
START TRANSACTION;
-- LOCK TABLES eln_fw_user_point_summary WRITE, eln_fw_user_point_detail WRITE;

#汇总表积分修改
IF trans_type='0' THEN #转入
	UPDATE eln_fw_user_point_summary SET transfer_in_point=transfer_in_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point WHERE user_id=to_userid AND is_deleted='0';
	SET @update_count=@update_count+row_count();
	#目标转入则来源转出
	IF get_from='user' THEN
		SET @real_update_count=2;
		UPDATE eln_fw_user_point_summary SET transfer_out_point=transfer_out_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point  WHERE user_id=get_from_id AND is_deleted='0';
		SET @update_count=@update_count+row_count();
	END IF;
ELSEIF trans_type='1' THEN #转出
	UPDATE eln_fw_user_point_summary SET transfer_out_point=transfer_out_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point  WHERE user_id=to_userid AND is_deleted='0';
	SET @update_count=@update_count+row_count();
	#目标转出则来源转入
	IF get_from='user' THEN
		SET @real_update_count=2;
		UPDATE eln_fw_user_point_summary SET transfer_in_point=transfer_in_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point  WHERE user_id=get_from_id AND is_deleted='0';
		SET @update_count=@update_count+row_count();
	END IF;
ELSEIF trans_type='2' THEN #获得
	SET @real_update_count=1;
	UPDATE eln_fw_user_point_summary SET get_point=get_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point  WHERE user_id=to_userid AND is_deleted='0';
	SET @update_count=@update_count+row_count();
ELSEIF trans_type='3' THEN #扣除
	SET @real_update_count=1;
	UPDATE eln_fw_user_point_summary SET deduct_point=deduct_point+trans_point,available_point=get_point+transfer_in_point-transfer_out_point-deduct_point  WHERE user_id=to_userid AND is_deleted='0';
	SET @update_count=@update_count+row_count();
END IF;


#汇总表若不能更新积分,则回滚
IF @update_count!=@real_update_count THEN 
	SET result = 51;
	SET Message = 'Transaction Error';
  SELECT result,message,cur_available_point;
	ROLLBACK;
	LEAVE LABEL_PRO;
END IF;

#转出后积分为负则回滚
SELECT available_point INTO @cur_available_point FROM eln_fw_user_point_summary WHERE user_id=to_userid AND is_deleted='0';
IF trans_type='1' THEN #转出
	IF @cur_available_point<0 THEN
		SET result = 71;
		SET Message = 'your available point is not correct';
    SELECT result,message,cur_available_point;
		ROLLBACK;
		LEAVE LABEL_PRO;
	END IF;
END IF;


SET @temp=reason;
#明细表增加记录
 -- SELECT upper(UUID()),UUID(),LENGTH(UUID()),CHAR_LENGTH(UUID())
#目标用户明细
IF reason='{user.real_name}' THEN
		SET @temp=@from_user_real_name; -- reason
END IF;
INSERT INTO `eln_fw_user_point_detail` (`kid`, `user_id`, `point_rule_id`, `company_id`, `reason`, `get_from`, `get_from_id`, `point`, `point_type`, `get_at`, `created_by`, `created_at`, `created_from`,`is_deleted`) 
VALUES (UPPER(UUID()), to_userid, point_rule_id, @to_cmpid, @temp, get_from, get_from_id, trans_point, trans_type, UNIX_TIMESTAMP(), 'Procedure', UNIX_TIMESTAMP(), 'Procedure', '0');


-- UNLOCK TABLES;

#源是user，则双向
IF get_from='user' THEN
	IF trans_type='0' THEN #转入
		SET @from_trans_type='1'; #源转出
	ELSEIF trans_type='1' THEN #转出
		SET @from_trans_type='0';	#源转入
	END IF;
	IF reason='{user.real_name}' THEN
		SET @temp=@to_user_real_name; -- reason
	END IF;
	#源用户明细
	INSERT INTO `eln_fw_user_point_detail` (`kid`, `user_id`, `point_rule_id`, `company_id`, `reason`, `get_from`, `get_from_id`, `point`, `point_type`, `get_at`, `created_by`, `created_at`, `created_from`,`is_deleted`) 
	VALUES (UPPER(UUID()), get_from_id, point_rule_id, @from_cmpid, @temp, get_from, to_userid, trans_point, @from_trans_type, UNIX_TIMESTAMP(), 'Procedure', UNIX_TIMESTAMP(), 'Procedure', '0');
END IF;

COMMIT;
SET result = 1;
SET message = 'Transaction Success';
SET cur_available_point=@cur_available_point;

SELECT result,message,cur_available_point;

END//
DELIMITER ;
 