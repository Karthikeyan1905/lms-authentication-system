<?php

use Illuminate\Support\Facades\Route;


Route::get('/login', function () {
    return view('auth.login');
})->name('login');


Route::get('/otp-verification', function () {
    return view('auth.otp');
})->name('otp.verification');


Route::get('/student-courses/{id}', function ($id) {
    return view('student.course-details', ['id' => $id]);
});

Route::get('/student-dashboard', function () {
    return view('student.dashboard');
})->name('student.dashboard');

Route::get('/school-admin-dashboard', function () {
    return view('school-admin.dashboard');
})->name('school-admin.dashboard');

Route::get('/school-admin/students/{id}', function ($id) {
    return view('school-admin.student-details', ['id' => $id]);
});

Route::get('/school-admin/add-student', function () {
    return view('school-admin.add-student');
});

Route::get('/school-admin/add-course', function () {
    return view('school-admin.add-course');
});

Route::get('/school-admin/courses/{id}', function ($id) {
    return view('school-admin.course-details', ['id' => $id]);
});