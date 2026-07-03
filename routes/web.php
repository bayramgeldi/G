<?php

use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AnonymousSubmissionController;
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
use App\Models\Entry;
use Illuminate\Support\Facades\Route;

Route::get('/', [EntryController::class, 'index'])->name('home');
Route::get('/sitemap.xml', function () {
    $urls = collect([
        route('home'),
        route('leaderboard'),
        route('roadmap'),
        route('governance.rules'),
        route('governance.log'),
    ]);

    Entry::query()
        ->visible()
        ->orderBy('id')
        ->each(function (Entry $entry) use ($urls) {
            $urls->push(route('entries.show', $entry));
        });

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

    foreach ($urls as $url) {
        $xml .= '    <url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_COMPAT, 'UTF-8').'</loc></url>'."\n";
    }

    $xml .= '</urlset>'."\n";

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');
Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /entries/create',
        'Disallow: /moderation/',
        'Disallow: /admin/',
        'Disallow: /dictionary/lookup',
        'Disallow: /dictionary/suggestions',
        'Sitemap: '.route('sitemap'),
    ];

    return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');
Route::get('/llms.txt', function () {
    $lines = [
        '# GMSS',
        '',
        'GMSS is a community-driven Turkmen slang dictionary with public entries, definitions, governance notes, and moderation transparency.',
        '',
        '## Canonical URLs',
        '- Home: '.route('home'),
        '- Sitemap: '.route('sitemap'),
        '- Rules: '.route('governance.rules'),
        '- Roadmap: '.route('roadmap'),
        '- Leaderboard: '.route('leaderboard'),
        '- Moderation log: '.route('governance.log'),
        '- Community export: '.route('export.json'),
        '',
        'Entries and definitions are community-provided and governed by the site moderation rules.',
    ];

    return response(implode("\n", $lines)."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('llms');
Route::get('/entries/create', [EntryController::class, 'create'])->name('entries.create');
Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');
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
Route::get('/moderation/anonymous-submissions', [AnonymousSubmissionController::class, 'index'])->middleware('auth')->name('moderation.anonymous-submissions');
Route::post('/moderation/anonymous-submissions/{submission}/vote', [AnonymousSubmissionController::class, 'vote'])->middleware('auth')->name('moderation.anonymous-submissions.vote');
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
