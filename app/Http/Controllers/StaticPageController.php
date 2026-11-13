<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller các trang tĩnh (A8): Giới thiệu, Chính sách, Liên hệ.
 */
class StaticPageController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function returnPolicy(): View
    {
        return view('pages.policy-return');
    }

    public function shippingPolicy(): View
    {
        return view('pages.policy-shipping');
    }

    public function privacyPolicy(): View
    {
        return view('pages.policy-privacy');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(ContactFormRequest $request): RedirectResponse
    {
        $this->contactService->sendInquiry($request->validated());

        return redirect()
            ->route('pages.contact')
            ->with('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.');
    }
}
