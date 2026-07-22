<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_too_large_exception_renders_a_clean_json_message_not_a_debug_page(): void
    {
        // Regression test for the stack-trace-leak finding: PHP throws this
        // before the request pipeline (and therefore any $request->validate())
        // ever runs, since the POST body already exceeded post_max_size by
        // the time PHP parsed it - so app/Exceptions/Handler.php must catch
        // it explicitly. Exercises the handler directly since PHPUnit's test
        // client doesn't go through real php.ini enforcement (see
        // tests/Feature/Workforce/EmployeeDocumentTest.php for the
        // app-level max:10240 rule, and the live curl verification for the
        // real server-config fix).
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $employee = Employee::factory()->create();

        $handler = app(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $request = \Illuminate\Http\Request::create(
            route('employees.documents.store', $employee->id),
            'POST',
            [],
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->actingAs($owner);

        $response = $handler->render($request, new PostTooLargeException());

        $this->assertSame(413, $response->getStatusCode());
        $this->assertSame(
            'The uploaded file is too large. Please choose a smaller file and try again.',
            json_decode($response->getContent(), true)['message']
        );
        $this->assertStringNotContainsString('Whoops', $response->getContent());
        $this->assertStringNotContainsString(__DIR__, $response->getContent());
    }
}
