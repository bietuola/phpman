<?php
declare(strict_types=1);

use Core\Route;

Route::group('/admin', function () {
    // 后台首页
    Route::get('/index', [Admin\Controller\Index::class, 'index'])->name('admin.index'); // 后台首页
});
