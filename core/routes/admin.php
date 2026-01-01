<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\PurchaserController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\FollowUpReportController;
use App\Http\Controllers\Admin\FollowUpLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;


use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Purchaser;

Route::namespace('Auth')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::controller('LoginController')->group(function () {
            Route::get('/', 'showLoginForm')->name('login');
            Route::post('/', 'login')->name('login');
            Route::get('logout', 'logout')->middleware('admin')->withoutMiddleware('admin.guest')->name('logout');
        });

        // Admin Password Reset
        Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
            Route::get('reset', 'showLinkRequestForm')->name('reset');
            Route::post('reset', 'sendResetCodeEmail');
            Route::get('code-verify', 'codeVerify')->name('code.verify');
            Route::post('verify-code', 'verifyCode')->name('verify.code');
        });

        Route::controller('ResetPasswordController')->group(function () {
            Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
            Route::post('password/reset/change', 'reset')->name('password.change');
        });
    });
});

Route::middleware('admin')->group(function () {
    // Admin User
    Route::controller(AdminUserController::class)->group(function () {
        Route::get('admins', 'index')->name('admins.index');
        Route::get('admins/create', 'create')->name('admins.create');
        Route::post('admins/store', 'store')->name('admins.store');
        Route::get('admins/{admin}/edit', 'edit')->name('admins.edit');
        Route::put('admins/{admin}', 'update')->name('admins.update');
        Route::delete('admins/{admin}', 'destroy')->name('admins.destroy');
        Route::get('admins/{admin}', 'show')->name('admins.show');
    });
    
    Route::controller(PermissionController::class)->group(function () {
        Route::get('permissions', 'index')->name('permissions.index');
        Route::post('permissions', 'store')->name('permissions.store');
        Route::delete('permissions/{permission}', 'destroy')->name('permissions.destroy');
    });


    Route::controller(RoleController::class)->group(function () {
        Route::get('roles', 'index')->name('roles.index');
        Route::get('roles/create', 'create')->name('roles.create');
        Route::post('roles/store', 'store')->name('roles.store');
        Route::get('roles/{role}/edit', 'edit')->name('roles.edit');
        Route::put('roles/{role}', 'update')->name('roles.update');
        Route::delete('roles/{role}', 'destroy')->name('roles.destroy');
        Route::get('roles/{role}', 'show')->name('roles.show');
    });


    
    Route::controller('AdminController')->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('sales-chart', 'salesChart')->name('chart.sales');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notifications/read-all', 'readAllNotification')->name('notifications.read.all');
        Route::post('notifications/delete-all', 'deleteAllNotification')->name('notifications.delete.all');
        Route::post('notifications/delete-single/{id}', 'deleteSingleNotification')->name('notifications.delete.single');

        //Report Bugs
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit');

        Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
        Route::get('print/{order}', 'printInvoice')->name('print.invoice');
    });

    // Banner
    Route::controller('PromoBannerController')->name('promo.banner.')->prefix('promotion/banner')->group(function () {
        Route::get('all', 'index')->name('index');
        Route::get('add', 'add')->name('add');
        Route::get('update/{id}', 'update')->name('update');
        Route::post('save/{id?}', 'save')->name('save');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    // Product Collection
    Route::controller('ProductCollectionController')->name('collection.')->prefix('collection')->group(function () {
        Route::get('all', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('update/{id}', 'update')->name('update');
        Route::post('save/{id?}', 'save')->name('save');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::post('delete-banner/{id}', 'deleteBanner')->name('banner.delete');
        Route::get('products', 'products')->name('products');
    });

    // Menu builder
    Route::controller('MenuBuilderController')->name('menu.builder.')->prefix('menu')->group(function () {
        Route::get('all', 'all')->name('all');
        Route::post('update-headers-order', 'updateHeadersOrder')->name('update.headers.order');

        Route::get('header-one', 'headerOne')->name('header.one');
        Route::post('header-one/update', 'updateHeaderOne')->name('header.one.update');

        Route::get('header-two', 'headerTwo')->name('header.two');
        Route::post('header-two/update', 'updateHeaderTwo')->name('header.two.update');

        Route::get('header-three', 'headerThree')->name('header.three');
        Route::post('header-three/update', 'updateHeaderThree')->name('header.three.update');

        Route::get('footer-menu', 'footerMenu')->name('footer');
        Route::post('footer-menu/update', 'updateFooterMenu')->name('footer.update');
    });

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('users')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('profile-completed', 'profileCompletedUsers')->name('profile.completed');
        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::post('update/{id}', 'update')->name('update');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('list', 'list')->name('list');
        Route::get('count-by-segment/{methodName}', 'countBySegment')->name('segment.count');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');
    });

    //Category Setting
    Route::controller('CategoryController')->name('category.')->prefix('categories')->group(function () {
        Route::get('/', 'index')->name('all');
        Route::get('trashed', 'trashed')->name('trashed');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('delete/{id}', 'delete')->name('delete');
        Route::post('category-menu-update', 'updatePosition')->name('update.position');
        Route::get('category/{id}', 'categoryById')->name('get.single');
        Route::get('check-slug/{id}', 'checkSlug')->name('check.slug');
        Route::post('force-delete/{id}', 'forceDelete')->name('force-delete');

    });

    //Brand
    Route::controller('BrandController')->prefix('brands')->name('brand.')->group(function () {
        Route::get('', 'index')->name('all');
        Route::get('trashed', 'trashed')->name('trashed');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('delete/{id}', 'delete')->name('delete');
    });

    //Product Attributes
    Route::controller('AttributeController')->prefix('product-attributes')->name('attribute.')->group(function () {
        Route::get('', 'index')->name('all');
        Route::get('values/{id}', 'values')->name('values');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('values/save/{id}', 'storeValues')->name('values.store');
        Route::post('update-status/{id}', 'status')->name('status');
    });

    // Product Type
    Route::controller('ProductTypeController')->prefix('product-types')
        ->name('product.type.')
        ->group(function () {
            Route::get('', 'index')->name('all');
            Route::get('create', 'create')->name('create');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('store/{id?}', 'store')->name('store');
          	Route::post('delete/{id}', 'destroy')->name('delete');
        });

    //Manage Products
    Route::prefix('product')->name('products.')->group(function () {

        Route::controller('ProductController')->group(function () {
            Route::get('create', 'create')->name('create');
            Route::get('edit/{id}', 'edit')->name('edit');
            Route::post('product-store/{id?}', 'store')->name('store');
            Route::post('delete-product/{id}', 'delete')->name('delete');
            Route::get('digital/download/{id}', 'digitalDownload')->name('digital.download');
            Route::post('generate-variants/{id}', 'generateVariants')->name('variants.generate');
            Route::post('publish-status/{id}', 'switchPublishStatus')->name('publish.status');
            Route::post('assign-media-to-attribute-value/{id}', 'assignMediaToAttributes')->name('media.assign');
        });



        Route::controller('ProductListController')->group(function () {
            Route::get('all', 'index')->name('all');
            Route::get('low-stocks', 'lowStock')->name('low.stock');
            Route::get('out-of-stocks', 'outOfStock')->name('out.of.stock');
            Route::get('trashed', 'trashed')->name('trashed');
            Route::get('top-selling', 'topSelling')->name('top.selling');
            Route::post('force-delete/{id}', 'forceDelete')->name('force-delete');

        });

        Route::controller('ProductStockController')->group(function () {
            Route::get('stock-log/{id}', 'stockLogByProduct')->name('stock.log');
            Route::get('variant/stock-log/{id}', 'stockLogByVariant')->name('stock.log.variant');
        });

        Route::controller('ProductReviewController')->group(function () {
            Route::prefix('reviews')->name('reviews.')->group(function () {
                Route::get('/', 'reviews')->name('index');
                Route::get('/trashed', 'trashedReviews')->name('trashed');
                Route::post('/{id}', 'reviewDelete')->name('delete');

                Route::get('view/{id}', 'view')->name('view');
              Route::post('status/{id}', 'changeStatus')->name('status');
            });
        });
    });

    Route::controller('MediaController')->prefix('media')->name('media')->group(function () {
        Route::get('/', 'media');
        Route::get('files', 'mediaFiles')->name('.files');
        Route::post('upload', 'upload')->name('.upload');
        Route::post('delete/{id}', 'delete')->name('.delete');
    });

    //Coupons
    Route::controller('CouponController')->prefix('promotion/coupon')->name('coupon.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('save/{id}', 'save')->name('store');
        Route::post('change-status/{id}', 'changeStatus')->name('status.change');
        Route::get('products', 'productsForCoupon')->name('products');
    });

    //Offers
    Route::controller('OfferController')->prefix('promotion/offer')->name('offer.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('save/  {id}', 'save')->name('store');
        Route::get('products', 'productsForOffer')->name('products');
        Route::post('status', 'changeStatus')->name('status');
    });

    // Subscriber
    Route::controller('SubscriberController')->prefix('subscriber')->name('subscriber.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('send-email', 'sendEmailForm')->name('send.email');
        Route::post('remove/{id}', 'remove')->name('remove');
        Route::post('send-email', 'sendEmail')->name('send.email');
    });

    //Shipping Methods
    Route::controller('ShippingMethodController')->prefix('shipping-method')->name('shipping.methods.')->group(function () {
        Route::get('/', 'index')->name('all');
        Route::get('create', 'create')->name('create');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('save/{id?}', 'store')->name('store');
        Route::post('switch-status/{id}', 'changeStatus')->name('status.switch');
    });

    //Order
    Route::controller('OrderController')->prefix('orders')->name('order.')->group(function () {
        Route::get('/', 'ordered')->name('index');
        Route::post('change-status/{id}', 'changeStatus')->name('status.change');
        Route::post('cancel-order/{id}', 'cancelStatus')->name('status.cancel');
        Route::get('pending', 'pending')->name('pending');
        Route::get('processing', 'onProcessing')->name('processing');
        Route::get('dispatched', 'dispatched')->name('dispatched');
        Route::get('delivered', 'deliveredOrders')->name('delivered');
        Route::get('canceled', 'canceledOrders')->name('canceled');
        Route::get('returned', 'returned')->name('returned');
        Route::get('cod', 'codOrders')->name('cod');
        Route::get('order-details/{id}', 'orderDetails')->name('details');

        Route::post('return/{id}', 'return')->name('return');
        Route::post('delete/{id}', [OrderController::class, 'delete'])->name('delete');
    });

    // Deposit Gateway
    Route::name('gateway.')->prefix('gateway')->group(function () {
        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->prefix('automatic')->name('automatic.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{code}', 'update')->name('update');
            Route::post('remove/{id}', 'remove')->name('remove');
            Route::post('status/{id}', 'status')->name('status');
        });

        // Manual Methods
        Route::controller('ManualGatewayController')->prefix('manual')->name('manual.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('new', 'store')->name('store');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });

    // DEPOSIT SYSTEM
    Route::controller('DepositController')->prefix('deposit')->name('deposit.')->group(function () {
        Route::get('all/{user_id?}', 'deposit')->name('list');
        Route::get('pending/{user_id?}', 'pending')->name('pending');
        Route::get('rejected/{user_id?}', 'rejected')->name('rejected');
        Route::get('approved/{user_id?}', 'approved')->name('approved');
        Route::get('successful/{user_id?}', 'successful')->name('successful');
        Route::get('initiated/{user_id?}', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');
    });

    Route::controller(CustomerController::class)
        ->prefix('customers')
        ->name('customers.')
        ->group(function () {

            // List + search + pagination
            Route::get('/', 'index')->name('index');

            // Create
            Route::post('/', 'store')->name('store');

            Route::get('/{customer}/edit',  'edit')->name('edit');

            // Update
            Route::put('/{customer}', 'update')->name('update');

            // Delete
            Route::delete('/{customer}', 'destroy')->name('destroy');

            // Export CSV / Excel
            Route::get('/export', 'export')->name('export');

            Route::get('/{customer}', 'show')->name('show');
        });

    /* -------------------------------------------------
 |  Daily Follow-Up CRUD
 |-------------------------------------------------*/
    Route::prefix('follow-ups')
        ->name('followups.')
        ->controller(FollowUpLogController::class)
        ->group(function () {

            // List + search + pagination
            Route::get('/',        'index')->name('index');

            // Create form
            Route::get('/create',  'create')->name('create');
            Route::get('report', [\App\Http\Controllers\Admin\FollowUpReportController::class, 'monthly'])
                ->name('report');

            Route::get(
                '/monthly-summaries',
                [\App\Http\Controllers\Admin\MonthlyFollowUpSummaryController::class, 'index']
            )
                ->name('summaries');

            Route::post('/monthly-summaries/{summary}/note', [\App\Http\Controllers\Admin\MonthlyFollowUpSummaryController::class, 'updateNote'])
                ->name('summaries.note.update');

            Route::get('/{log}',       'show')->name('show');

            // Store
            Route::post('/',       'store')->name('store');

            Route::get('/{log}/edit', 'edit')->name('edit');
            Route::put('/{log}',      'update')->name('update');

            // (Optional) Edit / Update / Delete
            // Route::get('/{log}/edit', 'edit')->name('edit');
            // Route::put('/{log}',      'update')->name('update');
            Route::delete('/{log}',   'destroy')->name('destroy');
        });

    /* -------------------------------------------------
 |  30-Day Report & Excel export
 |-------------------------------------------------*/
    // Route::get(
    //     'admin/follow-ups/report',
    //     [FollowUpReportController::class, 'monthly']
    // )->name('followups.report');

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
        Route::get('sales-report', 'salesReport')->name('sales');
        Route::get('business-report', 'businessReport')->name('business');
    });

    // Admin Support
    Route::controller('SupportTicketController')->prefix('ticket')->name('ticket.')->group(function () {
        Route::get('/', 'tickets')->name('index');
        Route::get('pending', 'pendingTicket')->name('pending');
        Route::get('closed', 'closedTicket')->name('closed');
        Route::get('answered', 'answeredTicket')->name('answered');
        Route::get('view/{id}', 'ticketReply')->name('view');
        Route::post('reply/{id}', 'replyTicket')->name('reply');
        Route::post('close/{id}', 'closeTicket')->name('close');
        Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
        Route::post('delete/{id}', 'ticketDelete')->name('delete');
    });

    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
        Route::get('get-keys', 'getKeys')->name('get.key');
    });

    Route::get('/admin/order/status-counts', [OrderController::class, 'statusCounts'])->name('order.status_counts');


    Route::controller('GeneralSettingController')->group(function () {

        Route::get('store-setting', 'systemSetting')->name('setting.system');

        // General Setting
        Route::get('general-setting', 'general')->name('setting.general');
        Route::post('general-setting', 'generalUpdate');

        Route::get('setting/social/credentials', 'socialiteCredentials')->name('setting.socialite.credentials');
        Route::post('setting/social/credentials/update/{key}', 'updateSocialiteCredential')->name('setting.socialite.credentials.update');
        Route::post('setting/social/credentials/status/{key}', 'updateSocialiteCredentialStatus')->name('setting.socialite.credentials.status.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon');

        //Custom CSS
        Route::get('custom-css', 'customCss')->name('setting.custom.css');
        Route::post('custom-css', 'customCssSubmit');

        Route::get('sitemap', 'sitemap')->name('setting.sitemap');
        Route::post('sitemap', 'sitemapSubmit');

        Route::get('robot', 'robot')->name('setting.robot');
        Route::post('robot', 'robotSubmit');

        //Cookie
        Route::get('cookie', 'cookie')->name('setting.cookie');
        Route::post('cookie', 'cookieSubmit');

        //maintenance_mode
        Route::get('maintenance-mode', 'maintenanceMode')->name('maintenance.mode');
        Route::post('maintenance-mode', 'maintenanceModeSubmit');

        // layout selection
        Route::get('homepage-layouts', 'homepageLayouts')->name('setting.home.layouts');
        Route::post('homepage-layouts', 'updateHomepageLayout')->name('setting.home.layouts.update');
    });

    //Notification Setting
    Route::name('setting.notification.')->controller('NotificationController')->prefix('notification')->group(function () {
        //Template Setting
        Route::get('global/email', 'globalEmail')->name('global.email');
        Route::post('global/email/update', 'globalEmailUpdate')->name('global.email.update');

        Route::get('global/sms', 'globalSms')->name('global.sms');
        Route::post('global/sms/update', 'globalSmsUpdate')->name('global.sms.update');

        Route::get('global/push', 'globalPush')->name('global.push');
        Route::post('global/push/update', 'globalPushUpdate')->name('global.push.update');

        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{type}/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{type}/{id}', 'templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');

        Route::get('notification/push/setting', 'pushSetting')->name('push');
        Route::post('notification/push/setting', 'pushSettingUpdate');
        Route::post('notification/push/setting/upload', 'pushSettingUpload')->name('push.upload');
        Route::get('notification/push/setting/download', 'pushSettingDownload')->name('push.download');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });


    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
        Route::get('system-update', 'systemUpdate')->name('update');
        Route::post('system-update', 'systemUpdateProcess')->name('update.process');
        Route::get('system-update/log', 'systemUpdateLog')->name('update.log');
    });


    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');


    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {

        Route::controller('FrontendController')->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key?}', 'frontendSections')->name('sections');
            Route::get('frontend-sections/{key?}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::get('frontend-slug-check/{key}/{id?}', 'frontendElementSlugCheck')->name('sections.element.slug.check');
            Route::get('frontend-element-seo/{key}/{id}', 'frontendSeo')->name('sections.element.seo');
            Route::post('frontend-element-seo/{key}/{id}', 'frontendSeoUpdate');
            Route::post('remove/{id}', 'remove')->name('remove');
        });

        // Page Builder
        Route::controller('PageBuilderController')->group(function () {
            Route::get('manage-pages', 'managePages')->name('manage.pages');
            Route::get('manage-pages/check-slug/{id?}', 'checkSlug')->name('manage.pages.check.slug');
            Route::post('manage-pages', 'managePagesSave')->name('manage.pages.save');
            Route::post('manage-pages/update', 'managePagesUpdate')->name('manage.pages.update');
            Route::post('manage-pages/delete/{id}', 'managePagesDelete')->name('manage.pages.delete');
            Route::get('manage-section/{id}', 'manageSection')->name('manage.section');
            Route::post('manage-section/{id}', 'manageSectionUpdate')->name('manage.section.update');

            Route::get('manage-seo/{id}', 'manageSeo')->name('manage.pages.seo');
            Route::post('manage-seo/{id}', 'manageSeoStore');
        });
    });

    // Generate SKU

    Route::post(
        'products/generate-sku',
        [\App\Http\Controllers\Admin\ProductController::class, 'generateSku']
    )->name('products.generate-sku');

    // Live Barcode
    Route::prefix('products')->group(function () {
        Route::post('barcode-preview', function (\Illuminate\Http\Request $r) {
            $sku = $r->input('sku');
            if (!$sku) return response()->json(['base64' => null]);

            $png = (new \Milon\Barcode\DNS1D)->getBarcodePNG($sku, 'C128');
            return response()->json(['base64' => $png]);
        })->name('products.barcode.preview');
    });

    Route::post('stock/receive', [StockController::class, 'receive'])
        ->name('stock.receive');

    Route::post(
        'purchasers/store',        //  POST  admin/purchasers/store
        [\App\Http\Controllers\Admin\PurchaserController::class, 'store']
    )->name('purchasers.store');

    // routes/admin.php
    Route::get('purchasers/search', [PurchaserController::class, 'select2'])
        ->name('purchasers.search');
});





        





use App\Http\Controllers\Admin\MultiExpressController;

Route::prefix('multi-express')->middleware(['auth:admin'])->name('')->group(function() {

    // Category
    Route::get('/categories', [MultiExpressController::class,'categoryIndex'])->name('multi_express.category.index');
    Route::get('/category/create', [MultiExpressController::class,'categoryCreate'])->name('multi_express.category.create');
    Route::post('/category/save/{id?}', [MultiExpressController::class,'categorySave'])->name('multi_express.category.save');
    Route::get('/category/edit/{id}', [MultiExpressController::class,'categoryEdit'])->name('multi_express.category.edit');
    Route::delete('/category/delete/{id}', [MultiExpressController::class,'categoryDelete'])->name('multi_express.category.delete');

    // Deal
    Route::get('/deals', [MultiExpressController::class,'dealIndex'])->name('multi_express.deal.index');
    Route::get('/deal/create', [MultiExpressController::class,'dealCreate'])->name('multi_express.deal.create');
    Route::post('/deal/save/{id?}', [MultiExpressController::class,'dealSave'])->name('multi_express.deal.save');
    Route::get('/deal/edit/{id}', [MultiExpressController::class,'dealEdit'])->name('multi_express.deal.edit');
    Route::delete('/deal/delete/{id}', [MultiExpressController::class,'dealDelete'])->name('multi_express.deal.delete');
    Route::get('/deals/{id}', [MultiExpressController::class, 'show'])->name('multi_express.deal.show');


    Route::get('/deal/{deal_id}/orders', [MultiExpressController::class,'orderIndex'])->name('admin.multi_express.order.index');
    Route::get('/deal/{deal_id}/order/{order_id}', [MultiExpressController::class,'orderShow'])->name('admin.multi_express.order.show');
    Route::post('/deal/{deal_id}/order/{order_id}/status', [MultiExpressController::class,'orderStatusUpdate'])->name('admin.multi_express.order.status');
    Route::delete('/deal/{deal_id}/order/{order_id}', [MultiExpressController::class,'orderDelete'])->name('admin.multi_express.order.delete');
    
    Route::get('admin/multi-express/order/{order}/payment', [MultiExpressController::class, 'showPaymentPage'])
    ->name('admin.multi_express.order.payment.page');
    Route::post('/order/payment/add', [MultiExpressController::class, 'addPayment'])->name('admin.multi_express.order.payment.add');
    

    // Multi Express Order Payments
    Route::get('/payments', [MultiExpressController::class, 'paymentIndex'])->name('admin.multi_express.payments');


});












Route::get('sales/download', [OrderController::class, 'download'])->name('sales.download');

Route::get('reports/business/csv', [ReportController::class, 'businessReportCsv'])->name('reports.business.csv');
Route::get('reports/sales/csv', [ReportController::class, 'salesReportCsv'])->name('reports.sales.csv');


// Route::get('/orders/download-excel', [OrderController::class, 'downloadExcel'])->name('admin.orders.download.excel');

// Route::get('reports/business/download', [ReportController::class, 'businessReportDownload'])->name('admin.reports.business.download');
