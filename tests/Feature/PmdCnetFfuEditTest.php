<?php

namespace Tests\Feature;

use App\Models\PmdCnetFfu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PmdCnetFfuEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_edit_page_can_be_rendered()
    {
        $equipment = PmdCnetFfu::factory()->create();

        $response = $this->get(route('pmd_cnet_ffu.edit', $equipment->id));

        $response->assertStatus(200);
        $response->assertViewIs('pmd_cnet_ffu.edit');
        $response->assertViewHas('equipment');
    }

    public function test_equipment_can_be_updated()
    {
        $equipment = PmdCnetFfu::factory()->create();

        $updatedData = [
            'Equipment' => 'Updated Equipment',
            'Parent' => $equipment->Parent,
            'Network' => $equipment->Network,
            'Port' => $equipment->Port,
            'CNX_Sequence' => $equipment->CNX_Sequence,
        ];

        $response = $this->put(route('pmd_cnet_ffu.update', $equipment->id), $updatedData);

        $response->assertRedirect(route('pmd_cnet_ffu.index'));
        $this->assertDatabaseHas('pmd_cnet_ffu', ['id' => $equipment->id, 'Equipment' => 'Updated Equipment']);
    }
}