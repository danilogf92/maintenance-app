<?php
namespace Tests\Feature;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class TableLoadPerformanceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    public function test_large_institution_page_response_size_and_render_time(): void
    {
        $this->bootWorkflow(); $now = now(); $records = [];
        for ($i = 1; $i <= 500; $i++) {
            $records[] = ['name' => sprintf('Performance %04d', $i), 'code' => 'PERF-'.$i, 'type' => 'hospital',
                'city' => 'Quito', 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('institutions')->insert($records);
        $start = hrtime(true); $response = $this->get(route('operations.institutions'))->assertOk();
        $content = $response->getContent();
        fwrite(STDOUT, sprintf("\nInstitutions 500: %.2fms; %d bytes; %d rendered rows\n", (hrtime(true)-$start)/1e6, strlen($content), preg_match_all('/<tr[^>]+data-record-id=/', $content)));
        $this->assertSame(Institution::count(), preg_match_all('/<tr[^>]+data-record-id=/', $content));
        $this->assertStringNotContainsString('goToTablePage', $content);
    }
}
