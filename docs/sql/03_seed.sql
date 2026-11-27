SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'welcome.view','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (2,'dashboard.view','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (3,'profile.view','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (4,'profile.update','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (5,'users.view','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (6,'users.create','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (7,'users.update','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (8,'users.delete','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (9,'users.unlock','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (10,'users.import','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (11,'sessions.revoke','web','2026-11-27 22:34:31','2026-11-27 22:34:31');

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (1,'admin','web','2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (2,'user','web','2026-11-27 22:34:31','2026-11-27 22:34:31');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (1,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (2,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (3,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (4,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (5,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (6,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (7,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (8,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (9,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (10,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (11,1);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (1,2);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (3,2);
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES (4,2);

INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (1,NULL,1,'welcome','Inicio','/welcome','home',10,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (2,NULL,2,'dashboard','Dashboard','/dashboard','chart',20,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (3,NULL,NULL,'users','Usuarios',NULL,'users',30,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (4,3,5,'users.list','Listado','/users','list',10,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (5,3,10,'users.import','Carga masiva','/users/import','upload',20,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');
INSERT INTO `menu_items` (`id`, `parent_id`, `permission_id`, `key`, `label`, `route`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (6,NULL,3,'profile','Mi perfil','/profile','user',40,1,'2026-11-27 22:34:31','2026-11-27 22:34:31');

INSERT INTO `persons` (`id`, `first_name`, `middle_name`, `first_surname`, `second_surname`, `identification`, `birth_date`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Ana','María','Torres','Vera','0923456781','1988-03-14','2026-11-27 22:34:31','2026-11-27 22:34:31',NULL);
INSERT INTO `persons` (`id`, `first_name`, `middle_name`, `first_surname`, `second_surname`, `identification`, `birth_date`, `created_at`, `updated_at`, `deleted_at`) VALUES (2,'Juan','Alberto','Piguave','Loor','1203574901','1992-07-21','2026-11-27 22:34:31','2026-11-27 22:34:31',NULL);

INSERT INTO `users` (`id`, `person_id`, `username`, `email`, `password`, `status`, `failed_login_attempts`, `locked_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,1,'Admin001','atorresv@mail.com','$2y$12$U.XKIqqAAND.dY.flCaAfOPgsqPBdkyFNNec.U.NnmQFhmZRmTXzm','active',0,NULL,'2026-11-27 22:34:31','2026-11-27 22:34:31',NULL);
INSERT INTO `users` (`id`, `person_id`, `username`, `email`, `password`, `status`, `failed_login_attempts`, `locked_at`, `created_at`, `updated_at`, `deleted_at`) VALUES (2,2,'Jpiguave01','jpiguavel@mail.com','$2y$12$c96iIVLCsejOoExZxhZbeuO2SjXbM6uwpJLYWZ4PGEeOQyh/YERlO','active',0,NULL,'2026-11-27 22:34:31','2026-11-27 22:34:31',NULL);

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (1,'App\\Models\\User',1);
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (2,'App\\Models\\User',2);
