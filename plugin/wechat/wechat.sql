SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `wechat_template_message` (
  `mid` int(11) UNSIGNED NOT NULL COMMENT '自增主键',
  `uuid` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `msgid` varchar(80) NOT NULL COMMENT '第三方消息id',
  `message` text NOT NULL COMMENT '消息体',
  `hash` varchar(60) NOT NULL COMMENT '消息读取sha1(openid+time+盐)',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '消息投递状态',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信模板消息';

CREATE TABLE `wechat_user` (
  `weid` int(11) UNSIGNED NOT NULL COMMENT '主键',
  `uuid` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `unionid` varchar(80) DEFAULT NULL COMMENT '微信开放平台',
  `openid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户标识',
  `user_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '用户类型',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '运营者对粉丝的备注',
  `subscribe` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '用户是否订阅公众号',
  `subscribe_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关注时间',
  `tagid_list` varchar(100) NOT NULL DEFAULT '' COMMENT '标签ID列表',
  `subscribe_scene` varchar(100) NOT NULL DEFAULT '' COMMENT '关注的渠道来源',
  `qr_scene` varchar(100) NOT NULL DEFAULT '' COMMENT '二维码扫码场景',
  `qr_scene_str` varchar(100) NOT NULL DEFAULT '' COMMENT '二维码扫码场景描述',
  `sync_time` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '同步时间',
  `token` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Token',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信用户表';


ALTER TABLE `wechat_template_message`
  ADD PRIMARY KEY (`mid`),
  ADD UNIQUE KEY `hash` (`hash`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `msg_uuid` (`uuid`);

ALTER TABLE `wechat_user`
  ADD PRIMARY KEY (`weid`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `openid` (`openid`,`user_type`),
  ADD KEY `uuid` (`uuid`);


ALTER TABLE `wechat_template_message`
  MODIFY `mid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键';

ALTER TABLE `wechat_user`
  MODIFY `weid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';


ALTER TABLE `wechat_template_message`
  ADD CONSTRAINT `msg_uuid` FOREIGN KEY (`uuid`) REFERENCES `wa_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `wechat_user`
  ADD CONSTRAINT `uuid` FOREIGN KEY (`uuid`) REFERENCES `wa_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
