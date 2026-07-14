<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\Pest\WithPest;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPTools\Approval\Enums\ApprovalFlowType;
use PHPTools\Approval\Facades\ApprovalFacade;
use PHPTools\Approval\Models\ApprovalTask;
use PHPTools\Approval\SimpleFlow;
use PHPTools\CommaSeparatedValues\CommaSeparatedValues;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

uses(PHPUnitTestCase::class)
    ->in('CommaSeparatedValues');

uses(OrchestraTestCase::class, WithPest::class, WithWorkbench::class, RefreshDatabase::class)
    ->beforeEach(fn() => config()->set('approval.enabled_in_console', true))
    ->in('LaravelApproval');

uses(OrchestraTestCase::class, WithPest::class, WithWorkbench::class, RefreshDatabase::class)
    ->in('LaravelCsvParser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// csv helpers

function makeCsv(string $path, array $options = []): CommaSeparatedValues
{
    $csv = new CommaSeparatedValues($path);

    $csv->setOptions($options);

    return $csv;
}

// laravel-approval helpers

function buildTaskFor(User $user, iterable $approvers, ApprovalFlowType $flowType = ApprovalFlowType::EVERY): ApprovalTask
{
    $article = Article::newModel();

    $flow = new SimpleFlow(
        title: 'Approve article',
        description: 'Review please',
        type: $flowType,
        expiresAt: new DateTimeImmutable('+1 day'),
        approvers: $approvers,
    );

    return ApprovalFacade::createTaskFor($article, $flow, $user);
}
