<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\AddressRequest;
use App\Services\AccountAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountAddressController extends Controller
{
    public function __construct(
        private readonly AccountAddressService $addressService
    ) {
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $this->addressService->createForUser(
            (int) $request->user()->getKey(),
            $request->validated()
        );

        return redirect()->route('account.index')->with('success', 'Đã lưu địa chỉ giao hàng.');
    }

    public function update(AddressRequest $request, int $id): RedirectResponse
    {
        $updated = $this->addressService->updateForUser(
            (int) $request->user()->getKey(),
            $id,
            $request->validated()
        );
        abort_if(! $updated, 404);

        return redirect()->route('account.index')->with('success', 'Đã cập nhật địa chỉ giao hàng.');
    }

    public function makeDefault(Request $request, int $id): RedirectResponse
    {
        abort_if($request->user() === null || $request->user()->isLocked(), 403);
        abort_if(! $this->addressService->makeDefault((int) $request->user()->getKey(), $id), 404);

        return redirect()->route('account.index')->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        abort_if($request->user() === null || $request->user()->isLocked(), 403);
        abort_if(! $this->addressService->deleteForUser((int) $request->user()->getKey(), $id), 404);

        return redirect()->route('account.index')->with('success', 'Đã xóa địa chỉ giao hàng.');
    }
}
