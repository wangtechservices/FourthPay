<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRolePermissionsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->smallIncrements('id')->comment('权限 ID');
            $table->smallInteger('parent_id')->default(0)->comment('父 ID');
            $table->string('rule', 64)->unique()->comment('权限规则');
            $table->string('name', 128)->unique()->comment('权限名称');
            $table->jsonb('extra')->default('{}')->comment('扩展字段，图标，前否前台显示');
            $table->string('description')->default('')->comment('描述与备注');
        });

        $this->__data();
    }

    private function __data() {
        $id = DB::table('admin_role_permissions')->insertGetId([
            'parent_id' => 0,
            'rule'      => 'permission',
            'name'      => '后台权限管理',
            'extra'     => json_encode([
                'icon'  => 'lock',
            ]),
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id' => $id,
                'rule'      => 'permission/index',
                'name'      => '权限管理',
                'extra'     => json_encode([
                    'icon'  => 'tree-table',
                ]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'permission/create',
                'name'      => '添加权限',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'permission/edit',
                'name'      => '编辑权限',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'permission/delete',
                'name'      => '删除权限',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'home',
                'name'      => 'home',
                'extra'     => json_encode(['hidden'=>true]),
            ]
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id' => $id,
                'rule'      => 'role/index',
                'name'      => '角色管理',
                'extra'     => json_encode([
                    'icon'  => 'peoples',
                ]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'role/create',
                'name'      => '添加角色',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'role/edit',
                'name'      => '编辑角色',
                'extra'     => json_encode(['hidden'=>true]),

            ],
            [
                'parent_id' => $id,
                'rule'      => 'role/delete',
                'name'      => '删除角色',
                'extra'     => json_encode(['hidden'=>true]),
            ]
        ]);

        DB::table('admin_role_permissions')->insert([
            [
                'parent_id' => $id,
                'rule'      => 'admin/index',
                'name'      => '管理员管理',
                'extra'     => json_encode([
                    'icon'  => 'user',
                ]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'admin/create',
                'name'      => '添加管理员',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'admin/edit',
                'name'      => '编辑管理员',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'admin/delete',
                'name'      => '删除管理员',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'admin/googlekey',
                'name'      => '解绑登录器',
                'extra'     => json_encode(['hidden'=>true]),
            ],
            [
                'parent_id' => $id,
                'rule'      => 'admin/lock',
                'name'      => '冻结管理员',
                'extra'     => json_encode(['hidden'=>true]),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('admin_role_permissions');
    }
}