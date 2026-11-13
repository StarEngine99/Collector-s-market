<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * Model người dùng tương thích với bảng `users` trong database/schema.sql.
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'MaNguoiDung';

    public const CREATED_AT = 'NgayTao';
    public const UPDATED_AT = 'NgayCapNhat';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'HoTen',
        'Email',
        'SoDienThoai',
        'MatKhau',
        'TrangThai',
        'XacThucEmailLuc',
        'MaGhiNho',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'MatKhau',
        'MaGhiNho',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'XacThucEmailLuc' => 'datetime',
            'NgayTao' => 'datetime',
            'NgayCapNhat' => 'datetime',
            'NgayXoa' => 'datetime',
        ];
    }

    /**
     * Trả về cột mật khẩu để Laravel Auth kiểm tra đăng nhập.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->MatKhau;
    }

    /**
     * Trả về tên cột lưu token ghi nhớ đăng nhập ("Remember Me").
     */
    public function getRememberTokenName(): string
    {
        return 'MaGhiNho';
    }

    /**
     * Trả về địa chỉ email dùng cho khôi phục mật khẩu.
     */
    public function getEmailForPasswordReset(): string
    {
        return (string) $this->Email;
    }

    /**
     * Kiểm tra tài khoản có đang bị khóa hay đã xóa mềm không.
     */
    public function isLocked(): bool
    {
        return $this->TrangThai === 'locked' || $this->NgayXoa !== null;
    }

    /**
     * Kiểm tra người dùng có vai trò cụ thể hay không (qua bảng user_roles & roles).
     */
    public function hasRole(string $roleName): bool
    {
        return DB::table('user_roles')
            ->join('roles', 'roles.MaVaiTro', '=', 'user_roles.MaVaiTro')
            ->where('user_roles.MaNguoiDung', $this->MaNguoiDung)
            ->where('roles.TenVaiTro', $roleName)
            ->exists();
    }

    /**
     * Kiểm tra người dùng có quyền cụ thể hay không (qua bảng user_roles, role_permissions & permissions).
     */
    public function hasPermission(string $permissionName): bool
    {
        return DB::table('user_roles')
            ->join('role_permissions', 'role_permissions.MaVaiTro', '=', 'user_roles.MaVaiTro')
            ->join('permissions', 'permissions.MaQuyen', '=', 'role_permissions.MaQuyen')
            ->where('user_roles.MaNguoiDung', $this->MaNguoiDung)
            ->where('permissions.TenQuyen', $permissionName)
            ->exists();
    }
}

