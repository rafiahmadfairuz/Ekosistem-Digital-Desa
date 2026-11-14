<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BumdesStaffController;
use App\Http\Controllers\Admin\PendingUserController;
use App\Http\Controllers\Admin\RegionAssignmentController;
use App\Http\Controllers\Admin\RegionCatalogController;
use App\Http\Controllers\Admin\ResidentRegionVerificationController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Bumdes\AssistedServiceSessionController;
use App\Http\Controllers\Bumdes\BusinessReportController;
use App\Http\Controllers\CorrespondenceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MarketplacePayoutAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResidentProfileController;
use App\Http\Controllers\ServicePortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:registration')->name('register.store');
    Route::get('/resend-verification', [EmailVerificationController::class, 'createResend'])->name('verification.resend.request');
    Route::post('/resend-verification', [EmailVerificationController::class, 'requestResend'])
        ->middleware('throttle:verification-resend-public')
        ->name('verification.resend.public');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:password-reset')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->middleware('throttle:6,1')->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::get('/products/{product}', [MarketplaceController::class, 'showProduct'])->name('products.show');
    Route::get('/products/{product}/images/{image}', [MarketplaceController::class, 'productImage'])->name('products.images.show');
});

Route::middleware(['auth', 'account.active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->whereNumber('notification')->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
        Route::get('/seller/application', [MarketplaceController::class, 'sellerApplication'])->name('seller.application');
        Route::post('/seller/application', [MarketplaceController::class, 'submitSellerApplication'])->name('seller.application.submit');
        Route::get('/cart', [MarketplaceController::class, 'cart'])->name('cart');
        Route::post('/cart/products/{product}', [MarketplaceController::class, 'addToCart'])->name('cart.add');
        Route::put('/cart/items/{item}', [MarketplaceController::class, 'updateCartItem'])->name('cart.items.update');
        Route::delete('/cart/items/{item}', [MarketplaceController::class, 'removeCartItem'])->name('cart.items.destroy');
        Route::get('/checkout', [MarketplaceController::class, 'checkoutForm'])->name('checkout');
        Route::post('/checkout', [MarketplaceController::class, 'checkout'])->middleware('throttle:service-request')->name('checkout.store');
        Route::post('/orders/{order}/payment-session', [MarketplaceController::class, 'startPaymentSession'])->middleware('throttle:service-request')->name('orders.payment-session');
        Route::post('/orders/group/{group}/payment-session', [MarketplaceController::class, 'startCheckoutGroupPaymentSession'])->middleware('throttle:service-request')->name('orders.group.payment-session');
        Route::get('/orders', [MarketplaceController::class, 'orders'])->name('orders.index');
        Route::get('/orders/group/{group}', [MarketplaceController::class, 'showCheckoutGroup'])->name('orders.group');
        Route::get('/orders/group/{group}/receipt', [MarketplaceController::class, 'checkoutReceipt'])->name('orders.group.receipt');
        Route::delete('/orders/group/{group}/history', [MarketplaceController::class, 'archiveCheckoutHistory'])->name('orders.group.history.destroy');
        Route::get('/orders/{order}', [MarketplaceController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [MarketplaceController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/{order}/receipt', [MarketplaceController::class, 'confirmReceipt'])->name('orders.receipt');
        Route::get('/seller/products', [MarketplaceController::class, 'sellerProducts'])->name('seller.products');
        Route::get('/seller/payout-account', [MarketplacePayoutAccountController::class, 'edit'])->name('seller.payout-account.edit');
        Route::put('/seller/payout-account', [MarketplacePayoutAccountController::class, 'store'])->name('seller.payout-account.update');
        Route::post('/seller/withdrawals', [MarketplacePayoutAccountController::class, 'requestWithdrawal'])->middleware('throttle:service-request')->name('seller.withdrawals.store');
        Route::get('/seller/products/archived', [MarketplaceController::class, 'archivedProducts'])->name('seller.products.archived');
        Route::post('/seller/products/{product}/restore', [MarketplaceController::class, 'restoreProduct'])->name('seller.products.restore');
        Route::get('/seller/products/{product}/edit', [MarketplaceController::class, 'editProduct'])->name('seller.products.edit');
        Route::post('/seller/products', [MarketplaceController::class, 'storeProduct'])->name('seller.products.store');
        Route::put('/seller/products/{product}', [MarketplaceController::class, 'updateProduct'])->name('seller.products.update');
        Route::delete('/seller/products/{product}/archive', [MarketplaceController::class, 'archiveProduct'])->name('seller.products.archive');
        Route::delete('/seller/images/{image}', [MarketplaceController::class, 'deleteProductImage'])->name('seller.images.destroy');
        Route::get('/seller/orders', [MarketplaceController::class, 'sellerOrders'])->name('seller.orders');
        Route::post('/seller/orders/{order}/decision', [MarketplaceController::class, 'sellerDecision'])->name('seller.orders.decision');
        Route::post('/seller/orders/{order}/advance', [MarketplaceController::class, 'advanceOrder'])->name('seller.orders.advance');
        Route::post('/seller/orders/{order}/delivery', [MarketplaceController::class, 'updateDelivery'])->name('seller.orders.delivery');
        Route::post('/seller/payments/{payment}/cod-confirmation', [MarketplaceController::class, 'confirmCod'])->name('seller.payments.cod-confirmation');
        Route::get('/management/sellers', [MarketplaceController::class, 'sellerApplications'])->name('management.sellers');
        Route::get('/management/payout-accounts', [MarketplacePayoutAccountController::class, 'managementIndex'])->name('management.payout-accounts');
        Route::post('/management/payout-accounts/{account}/review', [MarketplacePayoutAccountController::class, 'review'])->name('management.payout-accounts.review');
        Route::post('/management/sellers/{seller}/review', [MarketplaceController::class, 'reviewSeller'])->name('management.sellers.review');
        Route::get('/management/sellers/{seller}/documents/{document}', [MarketplaceController::class, 'sellerDocument'])->name('management.sellers.documents.show');
        Route::get('/management/categories', [MarketplaceController::class, 'categories'])->name('management.categories');
        Route::post('/management/categories', [MarketplaceController::class, 'storeCategory'])->name('management.categories.store');
        Route::put('/management/categories/{category}', [MarketplaceController::class, 'updateCategory'])->name('management.categories.update');
        Route::get('/management/products', [MarketplaceController::class, 'moderateProducts'])->name('management.products');
        Route::post('/management/products/{product}/review', [MarketplaceController::class, 'reviewProduct'])->name('management.products.review');
    });

    Route::middleware('role:Super Admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/marketplace/xendit-seller-accounts', [MarketplaceController::class, 'xenditSellerAccounts'])->name('marketplace.xendit-seller-accounts.index');
        Route::get('/users', [UserAccessController::class, 'index'])->name('users.manage');
        Route::get('/users/village-head/replacement', [UserAccessController::class, 'villageHeadReplacement'])->name('users.village-head.replacement');
        Route::put('/users/{user}/roles', [UserAccessController::class, 'updateRoles'])->name('users.roles.update');
        Route::post('/users/village-head/replace', [UserAccessController::class, 'replaceVillageHead'])->name('users.village-head.replace');
        Route::put('/users/{user}/status', [UserAccessController::class, 'updateStatus'])->name('users.status.update');
        Route::delete('/users/{user}', [UserAccessController::class, 'destroy'])->name('users.destroy');
        Route::get('/region-assignments', [RegionAssignmentController::class, 'index'])->name('region-assignments.index');
        Route::post('/region-assignments', [RegionAssignmentController::class, 'store'])->name('region-assignments.store');
        Route::delete('/region-assignments/{assignment}', [RegionAssignmentController::class, 'revoke'])->name('region-assignments.revoke');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('role:BUMDes Manager')->prefix('bumdes')->name('bumdes.')->group(function (): void {
        Route::get('/staff', [BumdesStaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [BumdesStaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{staff}', [BumdesStaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}', [BumdesStaffController::class, 'destroy'])->name('staff.destroy');
        Route::get('/reports', [BusinessReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/expenses', [BusinessReportController::class, 'storeExpense'])->name('reports.expenses.store');
        Route::post('/reports/expenses/{expense}/void', [BusinessReportController::class, 'voidExpense'])->name('reports.expenses.void');
    });

    Route::middleware('role:BUMDes Manager,BUMDes Operator,Resident')->prefix('bumdes/assistance')->name('bumdes.assistance.')->group(function (): void {
        Route::get('/', [AssistedServiceSessionController::class, 'index'])->name('index');
        Route::get('/create', [AssistedServiceSessionController::class, 'create'])->name('create');
        Route::post('/', [AssistedServiceSessionController::class, 'store'])->middleware('throttle:service-request')->name('store');
        Route::get('/{session}', [AssistedServiceSessionController::class, 'show'])->name('show');
        Route::post('/{session}/messages', [AssistedServiceSessionController::class, 'sendMessage'])->middleware('throttle:service-request')->name('messages.store');
        Route::post('/{session}/accept', [AssistedServiceSessionController::class, 'accept'])->name('accept');
        Route::post('/{session}/reject', [AssistedServiceSessionController::class, 'reject'])->name('reject');
        Route::put('/{session}/reference', [AssistedServiceSessionController::class, 'attachReference'])->name('reference.update');
        Route::post('/{session}/request-confirmation', [AssistedServiceSessionController::class, 'requestConfirmation'])->name('request-confirmation');
        Route::post('/{session}/confirm', [AssistedServiceSessionController::class, 'confirm'])->name('confirm');
        Route::post('/{session}/cancel', [AssistedServiceSessionController::class, 'cancel'])->name('cancel');
    });

    Route::middleware('role:BUMDes Manager')->prefix('bumdes')->name('bumdes.')->group(function (): void {
        Route::get('/assistance-reports', [AssistedServiceSessionController::class, 'reports'])->name('assistance.reports');
    });

    Route::get('/account/profile', [ResidentProfileController::class, 'show'])->name('profile.show');
    Route::get('/account/profile/create', [ResidentProfileController::class, 'create'])->name('profile.create');
    Route::post('/account/profile', [ResidentProfileController::class, 'store'])->name('profile.store');
    Route::get('/account/profile/edit', [ResidentProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/account/profile', [ResidentProfileController::class, 'update'])->name('profile.update');
    Route::get('/officer/residents', [ResidentProfileController::class, 'regionalIndex'])->middleware('role:RT,RW')->name('officer.residents.index');
    Route::get('/officer/residents/{resident}', [ResidentProfileController::class, 'review'])->middleware('role:RT,RW')->name('officer.residents.show');

    Route::prefix('correspondence')->name('correspondence.')->group(function (): void {
        Route::get('/', [CorrespondenceController::class, 'index'])->name('index');
        Route::get('/create', [CorrespondenceController::class, 'create'])->name('create');
        Route::post('/', [CorrespondenceController::class, 'store'])->name('store');
        Route::get('/types', [CorrespondenceController::class, 'types'])->name('types');
        Route::post('/notifications/{notification}/read', [CorrespondenceController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/types', [CorrespondenceController::class, 'storeType'])->name('types.store');
        Route::put('/types/{letterType}', [CorrespondenceController::class, 'updateType'])->name('types.update');
        Route::get('/{letterRequest}', [CorrespondenceController::class, 'show'])->name('show');
        Route::post('/{letterRequest}/confirm', [CorrespondenceController::class, 'confirm'])->name('confirm');
        Route::put('/{letterRequest}/revise', [CorrespondenceController::class, 'revise'])->name('revise');
        Route::post('/{letterRequest}/decision', [CorrespondenceController::class, 'decide'])->name('decision');
        Route::post('/{letterRequest}/signature', [CorrespondenceController::class, 'sign'])->name('signature');
        Route::get('/{letterRequest}/attachments/{attachment}', [CorrespondenceController::class, 'downloadAttachment'])->name('attachments.download');
        Route::get('/{letterRequest}/documents/{document}', [CorrespondenceController::class, 'downloadDocument'])->name('documents.download');
    });

    Route::middleware('role:Resident,BUMDes Manager,BUMDes Operator')->prefix('services')->name('services.')->group(function (): void {
        Route::get('/', [ServicePortalController::class, 'index'])->name('index');
        Route::get('/management', [ServicePortalController::class, 'management'])->name('management');
        Route::post('/installation-requests', [ServicePortalController::class, 'requestInstallation'])->middleware('throttle:service-request')->name('installations.store');
        Route::post('/invoices/{invoice}/payments', [ServicePortalController::class, 'submitPayment'])->middleware('throttle:service-request')->name('payments.store');
        Route::post('/service-requests', [ServicePortalController::class, 'createServiceRequest'])->middleware('throttle:service-request')->name('requests.store');
        Route::post('/wifi/{wifiNetwork}/sessions', [ServicePortalController::class, 'openWifiSession'])->middleware('throttle:wifi-session')->name('wifi.sessions.store');
        Route::post('/installations/{installationRequest}/review', [ServicePortalController::class, 'reviewInstallation'])->name('installations.review');
        Route::post('/payments/{payment}/review', [ServicePortalController::class, 'confirmPayment'])->name('payments.review');
        Route::put('/subscriptions/{internetSubscription}/status', [ServicePortalController::class, 'updateSubscriptionStatus'])->name('subscriptions.status');
        Route::post('/service-requests/{serviceRequest}/status', [ServicePortalController::class, 'updateServiceRequest'])->name('requests.update');
        Route::post('/packages', [ServicePortalController::class, 'storePackage'])->name('packages.store');
        Route::put('/packages/{internetPackage}', [ServicePortalController::class, 'updatePackage'])->name('packages.update');
        Route::post('/wifi/networks', [ServicePortalController::class, 'storeNetwork'])->name('wifi.networks.store');
        Route::put('/wifi/networks/{wifiNetwork}', [ServicePortalController::class, 'updateNetwork'])->name('wifi.networks.update');
    });

    Route::middleware('role:Super Admin,Village Admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/village-settings', [RegionCatalogController::class, 'settings'])->name('village-settings.index');
        Route::get('/villages/{village}/edit', [RegionCatalogController::class, 'editVillage'])->name('villages.edit');
        Route::put('/villages/{village}', [RegionCatalogController::class, 'updateVillage'])->name('villages.update');
        Route::delete('/villages/{village}', [RegionCatalogController::class, 'destroyVillage'])->name('villages.destroy');
    });

    Route::middleware('role:Super Admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/regions', [RegionCatalogController::class, 'index'])->name('regions.index');
        Route::post('/villages', [RegionCatalogController::class, 'storeVillage'])->name('villages.store');
        Route::post('/regions/rw', [RegionCatalogController::class, 'storeRw'])->name('regions.rw.store');
        Route::post('/regions/rt', [RegionCatalogController::class, 'storeRt'])->name('regions.rt.store');
        Route::delete('/regions/{region}', [RegionCatalogController::class, 'destroyRegion'])->name('regions.destroy');
        Route::get('/users/pending', [PendingUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/activate', [PendingUserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/resend-verification', [PendingUserController::class, 'resendVerification'])
            ->middleware('throttle:verification-resend')
            ->name('users.resend-verification');
    });

    Route::middleware('role:Village Admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/resident-region-verification', [ResidentRegionVerificationController::class, 'index'])->name('resident-region-verification.index');
        Route::post('/resident-region-verification/{resident}', [ResidentRegionVerificationController::class, 'verify'])->name('resident-region-verification.verify');
    });
});
