<?php

use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // base tables
        Encore\Admin\Auth\Database\Menu::truncate();
        Encore\Admin\Auth\Database\Menu::insert(
            [
                [
                    "parent_id" => 0,
                    "order" => 1,
                    "title" => "仪表盘",
                    "icon" => "fa-bar-chart",
                    "uri" => "/",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 2,
                    "title" => "管理员",
                    "icon" => "fa-tasks",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 3,
                    "title" => "管理员列表",
                    "icon" => "fa-users",
                    "uri" => "auth/users",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 4,
                    "title" => "角色管理",
                    "icon" => "fa-user",
                    "uri" => "auth/roles",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 5,
                    "title" => "权限管理",
                    "icon" => "fa-ban",
                    "uri" => "auth/permissions",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 6,
                    "title" => "菜单管理",
                    "icon" => "fa-bars",
                    "uri" => "auth/menu",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 2,
                    "order" => 7,
                    "title" => "操作日志",
                    "icon" => "fa-history",
                    "uri" => "auth/logs",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 13,
                    "title" => "会员管理",
                    "icon" => "fa-users",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 8,
                    "order" => 14,
                    "title" => "会员列表",
                    "icon" => "fa-user",
                    "uri" => "users",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 8,
                    "order" => 15,
                    "title" => "资产列表",
                    "icon" => "fa-balance-scale",
                    "uri" => "user-assets",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 18,
                    "title" => "合约交易",
                    "icon" => "fa-strikethrough",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 11,
                    "order" => 19,
                    "title" => "委托订单",
                    "icon" => "fa-bars",
                    "uri" => "contract-entrusts",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 11,
                    "order" => 20,
                    "title" => "持仓订单",
                    "icon" => "fa-bars",
                    "uri" => "contract-positions",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 11,
                    "order" => 21,
                    "title" => "平仓订单",
                    "icon" => "fa-bars",
                    "uri" => "contract-trans",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 22,
                    "title" => "币币交易",
                    "icon" => "fa-bitcoin",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 15,
                    "order" => 23,
                    "title" => "订单列表",
                    "icon" => "fa-bars",
                    "uri" => "exchange-orders",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 24,
                    "title" => "法币交易",
                    "icon" => "fa-adjust",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 17,
                    "order" => 28,
                    "title" => "申诉订单",
                    "icon" => "fa-bars",
                    "uri" => "fb-appeals",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 17,
                    "order" => 25,
                    "title" => "求购订单",
                    "icon" => "fa-bars",
                    "uri" => "fb-buyings",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 17,
                    "order" => 26,
                    "title" => "出售订单",
                    "icon" => "fa-bars",
                    "uri" => "fb-sells",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 17,
                    "order" => 27,
                    "title" => "匹配订单",
                    "icon" => "fa-bars",
                    "uri" => "fb-trans",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 17,
                    "order" => 29,
                    "title" => "商家申请",
                    "icon" => "fa-bars",
                    "uri" => "fb-shop-applies",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 30,
                    "title" => "充提管理",
                    "icon" => "fa-tachometer",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 23,
                    "order" => 32,
                    "title" => "提币记录",
                    "icon" => "fa-bars",
                    "uri" => "user-withdraw-records",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 23,
                    "order" => 31,
                    "title" => "提币地址",
                    "icon" => "fa-bars",
                    "uri" => "user-withdraw-addresses",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 8,
                    "title" => "系统设置",
                    "icon" => "fa-sliders",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 26,
                    "order" => 12,
                    "title" => "轮播图",
                    "icon" => "fa-bars",
                    "uri" => "system-slides",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 26,
                    "order" => 10,
                    "title" => "系统公告",
                    "icon" => "fa-bars",
                    "uri" => "system-posts",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 26,
                    "order" => 11,
                    "title" => "系统协议",
                    "icon" => "fa-bars",
                    "uri" => "system-agrees",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 16,
                    "title" => "财务记录",
                    "icon" => "fa-money",
                    "uri" => NULL,
                    "permission" => NULL
                ],
                [
                    "parent_id" => 30,
                    "order" => 17,
                    "title" => "资金流水",
                    "icon" => "fa-recycle",
                    "uri" => "user-money-logs",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 0,
                    "order" => 33,
                    "title" => "Log viewer",
                    "icon" => "fa-database",
                    "uri" => "logs",
                    "permission" => NULL
                ],
                [
                    "parent_id" => 26,
                    "order" => 9,
                    "title" => "Configx",
                    "icon" => "fa-toggle-on",
                    "uri" => "configx/edit",
                    "permission" => NULL
                ]
            ]
        );

        Encore\Admin\Auth\Database\Permission::truncate();
        Encore\Admin\Auth\Database\Permission::insert(
            [
                [
                    "name" => "All permission",
                    "slug" => "*",
                    "http_method" => "",
                    "http_path" => "*"
                ],
                [
                    "name" => "Dashboard",
                    "slug" => "dashboard",
                    "http_method" => "GET",
                    "http_path" => "/"
                ],
                [
                    "name" => "Login",
                    "slug" => "auth.login",
                    "http_method" => "",
                    "http_path" => "/auth/login\n/auth/logout"
                ],
                [
                    "name" => "User setting",
                    "slug" => "auth.setting",
                    "http_method" => "GET,PUT",
                    "http_path" => "/auth/setting"
                ],
                [
                    "name" => "Auth management",
                    "slug" => "auth.management",
                    "http_method" => "",
                    "http_path" => "/auth/roles\n/auth/permissions\n/auth/menu\n/auth/logs"
                ],
                [
                    "name" => "Logs",
                    "slug" => "ext.log-viewer",
                    "http_method" => "",
                    "http_path" => "/logs*"
                ],
                [
                    "name" => "Admin Configx",
                    "slug" => "ext.configx",
                    "http_method" => "",
                    "http_path" => "/configx/*"
                ]
            ]
        );

        Encore\Admin\Auth\Database\Role::truncate();
        Encore\Admin\Auth\Database\Role::insert(
            [
                [
                    "name" => "Administrator",
                    "slug" => "administrator"
                ]
            ]
        );

        // pivot tables
        DB::table('admin_role_menu')->truncate();
        DB::table('admin_role_menu')->insert(
            [
                [
                    "role_id" => 1,
                    "menu_id" => 2
                ]
            ]
        );

        DB::table('admin_role_permissions')->truncate();
        DB::table('admin_role_permissions')->insert(
            [
                [
                    "role_id" => 1,
                    "permission_id" => 1
                ]
            ]
        );

        // users tables
        Encore\Admin\Auth\Database\Administrator::truncate();
        Encore\Admin\Auth\Database\Administrator::insert(
            [
                [
                    "username" => "admin",
                    "password" => "\$2y\$10\$9cuEBEpvZ10qmzjecEbmuu8GjQcgcLc.SJBgo07AYan0zm5d9Ww6m",
                    "name" => "Administrator",
                    "avatar" => "",
                    "remember_token" => "oVFZtuJb2RHVu5j0sYIqkDCQpAbwQevZZrcH04JH8wQstHqGkxbSGK8SCr9B"
                ]
            ]
        );

        DB::table('admin_role_users')->truncate();
        DB::table('admin_role_users')->insert(
            [
                [
                    "role_id" => 1,
                    "user_id" => 1
                ]
            ]
        );

        DB::table('admin_user_permissions')->truncate();
        DB::table('admin_user_permissions')->insert(
            [

            ]
        );

        // finish
    }
}
