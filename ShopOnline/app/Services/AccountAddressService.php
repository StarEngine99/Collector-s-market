<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountAddressService
{
    /**
     * @return Collection<int, object>
     */
    public function listForUser(int $userId): Collection
    {
        return DB::table('addresses')
            ->where('MaNguoiDung', $userId)
            ->orderByDesc('MacDinh')
            ->orderByDesc('MaDiaChi')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createForUser(int $userId, array $data): int
    {
        return DB::transaction(function () use ($userId, $data): int {
            $now = Carbon::now();
            $makeDefault = (bool) ($data['MacDinh'] ?? false)
                || ! DB::table('addresses')->where('MaNguoiDung', $userId)->exists();

            if ($makeDefault) {
                DB::table('addresses')
                    ->where('MaNguoiDung', $userId)
                    ->update(['MacDinh' => false, 'NgayCapNhat' => $now]);
            }

            return (int) DB::table('addresses')->insertGetId([
                'MaNguoiDung' => $userId,
                ...$this->addressFields($data),
                'MacDinh' => $makeDefault,
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateForUser(int $userId, int $addressId, array $data): bool
    {
        return DB::transaction(function () use ($userId, $addressId, $data): bool {
            $address = DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->lockForUpdate()
                ->first();

            if ($address === null) {
                return false;
            }

            $now = Carbon::now();
            $makeDefault = (bool) ($data['MacDinh'] ?? false);
            if ($makeDefault) {
                DB::table('addresses')
                    ->where('MaNguoiDung', $userId)
                    ->update(['MacDinh' => false, 'NgayCapNhat' => $now]);
            }

            DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->update([
                    ...$this->addressFields($data),
                    'MacDinh' => $makeDefault || (bool) $address->MacDinh,
                    'NgayCapNhat' => $now,
                ]);

            return true;
        });
    }

    public function makeDefault(int $userId, int $addressId): bool
    {
        return DB::transaction(function () use ($userId, $addressId): bool {
            $addressExists = DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->exists();

            if (! $addressExists) {
                return false;
            }

            $now = Carbon::now();
            DB::table('addresses')
                ->where('MaNguoiDung', $userId)
                ->update(['MacDinh' => false, 'NgayCapNhat' => $now]);

            DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->update(['MacDinh' => true, 'NgayCapNhat' => $now]);

            return true;
        });
    }

    public function deleteForUser(int $userId, int $addressId): bool
    {
        return DB::transaction(function () use ($userId, $addressId): bool {
            $address = DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->lockForUpdate()
                ->first();

            if ($address === null) {
                return false;
            }

            DB::table('addresses')
                ->where('MaDiaChi', $addressId)
                ->where('MaNguoiDung', $userId)
                ->delete();

            if ((bool) $address->MacDinh) {
                DB::table('addresses')
                    ->where('MaNguoiDung', $userId)
                    ->update(['MacDinh' => false, 'NgayCapNhat' => Carbon::now()]);

                $nextDefault = DB::table('addresses')
                    ->where('MaNguoiDung', $userId)
                    ->orderByDesc('MaDiaChi')
                    ->first();

                if ($nextDefault !== null) {
                    DB::table('addresses')
                        ->where('MaDiaChi', $nextDefault->MaDiaChi)
                        ->update(['MacDinh' => true, 'NgayCapNhat' => Carbon::now()]);
                }
            }

            return true;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function addressFields(array $data): array
    {
        return [
            'TenNguoiNhan' => trim((string) $data['TenNguoiNhan']),
            'SoDienThoai' => trim((string) $data['SoDienThoai']),
            'TinhThanh' => trim((string) $data['TinhThanh']),
            'QuanHuyen' => trim((string) $data['QuanHuyen']),
            'PhuongXa' => trim((string) $data['PhuongXa']),
            'DiaChiChiTiet' => trim((string) $data['DiaChiChiTiet']),
        ];
    }
}
