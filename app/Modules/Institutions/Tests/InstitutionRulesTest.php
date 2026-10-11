<?php

namespace App\Modules\Institutions\Tests;

use App\Models\Institution;
use App\Modules\Institutions\Configuration\Rules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class InstitutionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_is_unique_but_the_same_record_can_keep_its_code(): void
    {
        $data = ['name' => 'Centro', 'code' => 'UNIQUE', 'type' => 'clinic'];
        $item = Institution::create($data);
        $this->assertFalse(Validator::make($data, Rules::validation())->passes());
        $this->assertTrue(Validator::make($data, Rules::validation($item))->passes());
    }

    public function test_invalid_categories_and_email_are_rejected_on_the_server(): void
    {
        $validator = Validator::make(['name' => 'Centro', 'code' => 'CODE',
            'type' => 'forged', 'contact_email' => 'invalid'], Rules::validation());
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('type'));
        $this->assertTrue($validator->errors()->has('contact_email'));
    }
}
