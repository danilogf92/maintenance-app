<?php
namespace Database\Seeders;
use App\Models\Institution;
use App\Modules\Clients\Models\Client;
use App\Modules\Contacts\Models\{Contact, ContactLink};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ContactDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('testing')) { $this->command?->warn('La carga de demostraciones está deshabilitada.'); return; }

        DB::transaction(function () {
            $client = Client::where('code', 'DEMO-CLI-1')->lockForUpdate()->first();
            $institution = Institution::where('code', 'DEMO-INS-1')->lockForUpdate()->first();
            if (!$client || !$institution) { return; }
            $technical = Contact::firstOrCreate(['code' => 'DEMO-CTO-1'], ['name' => 'DEMO · Ana Torres', 'position' => 'Responsable de ingeniería clínica',
                'department' => 'Mantenimiento biomédico', 'email' => 'ana.torres@example.com', 'phone' => '000-000-0021', 'extension' => '120', 'active' => true]);
            $administrative = Contact::firstOrCreate(['code' => 'DEMO-CTO-2'], ['name' => 'DEMO · Luis Molina', 'position' => 'Coordinador administrativo',
                'department' => 'Administración', 'email' => 'luis.molina@example.com', 'phone' => '000-000-0022', 'extension' => '210', 'active' => true]);
            $this->link($technical, 'client', $client->id, 'technical');
            $this->link($technical, 'institution', $institution->id, 'technical');
            $this->link($administrative, 'client', $client->id, 'administrative');
        });
    }
    private function link(Contact $contact, string $type, int $id, string $category): void
    {
        $column = $type === 'client' ? 'client_id' : 'institution_id';
        ContactLink::firstOrCreate(['contact_id' => $contact->id, $column => $id], ['category' => $category, 'active' => true, 'is_primary' => false]);
    }
}
