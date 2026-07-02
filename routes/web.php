<?php

use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DefinitionController;
use App\Http\Controllers\DictionaryLookupController;
use App\Http\Controllers\DictionarySuggestionController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ModerationLogController;
use App\Http\Controllers\ModerationReportController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EntryController::class, 'index'])->name('home');
Route::get('/entries/create', [EntryController::class, 'create'])->middleware('auth')->name('entries.create');
Route::post('/entries', [EntryController::class, 'store'])->middleware('auth')->name('entries.store');
Route::get('/entries/{entry:slug}', [EntryController::class, 'show'])->name('entries.show');
Route::post('/entries/{entry:slug}/definitions', [DefinitionController::class, 'store'])->middleware('auth')->name('definitions.store');
Route::post('/entries/{entry:slug}/report', [ModerationReportController::class, 'reportEntry'])->middleware('auth')->name('entries.report');
Route::post('/entries/{entry:slug}/appeal', [AppealController::class, 'appealEntry'])->middleware('auth')->name('entries.appeal');

Route::post('/definitions/{definition}/vote', [VoteController::class, 'store'])->middleware('auth')->name('definitions.vote');
Route::post('/definitions/{definition}/report', [ModerationReportController::class, 'reportDefinition'])->middleware('auth')->name('definitions.report');
Route::post('/definitions/{definition}/appeal', [AppealController::class, 'appealDefinition'])->middleware('auth')->name('definitions.appeal');
Route::post('/appeals/{appeal}/vote', [AppealController::class, 'vote'])->middleware('auth')->name('appeals.vote');
Route::get('/leaderboard', LeaderboardController::class)->name('leaderboard');
Route::get('/roadmap', RoadmapController::class)->name('roadmap');
Route::get('/governance/rules', RulesController::class)->name('governance.rules');
Route::get('/governance/log', ModerationLogController::class)->name('governance.log');
Route::get('/export.json', ExportController::class)->name('export.json');
Route::get('/dictionary/lookup', DictionaryLookupController::class)->name('dictionary.lookup');
Route::get('/dictionary/suggestions', DictionarySuggestionController::class)->name('dictionary.suggestions');

Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::patch('/admin/entries/{entry}/hide', [AdminContentController::class, 'hideEntry'])->middleware(['auth', 'admin'])->name('admin.entries.hide');
Route::patch('/admin/definitions/{definition}/hide', [AdminContentController::class, 'hideDefinition'])->middleware(['auth', 'admin'])->name('admin.definitions.hide');
