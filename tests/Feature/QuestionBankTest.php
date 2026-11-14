<?php

namespace Tests\Feature;

use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Student Test',
            'email' => 'student@example.com',
            'role' => 'user',
        ]);
    }

public function test_admin_can_manage_question_banks(): void
    {
        
        $createResponse = $this->actingAs($this->admin)->post(route('admin.question-banks.store'), [
            'title' => 'Bank Soal TPS Kuantitatif 2025',
            'category' => 'TPS',
            'description' => 'Kumpulan foto soal TPS kuantitatif untuk persiapan UTBK.',
            'is_active' => '1',
        ]);

        $bank = QuestionBank::where('title', 'Bank Soal TPS Kuantitatif 2025')->first();
        $this->assertNotNull($bank);
        $this->assertEquals('TPS', $bank->category);
        $this->assertTrue($bank->is_active);
        $createResponse->assertRedirect(route('admin.question-banks.items', $bank));

$updateResponse = $this->actingAs($this->admin)->put(route('admin.question-banks.update', $bank), [
            'title' => 'Bank Soal TPS Kuantitatif & Penalaran 2025',
            'category' => 'Penalaran Umum',
            'description' => 'Deskripsi diperbarui.',
            'is_active' => '1',
        ]);

        $updateResponse->assertSessionHas('success');
        $bank->refresh();
        $this->assertEquals('Bank Soal TPS Kuantitatif & Penalaran 2025', $bank->title);
        $this->assertEquals('Penalaran Umum', $bank->category);

$this->actingAs($this->admin)->patch(route('admin.question-banks.toggle', $bank));
        $bank->refresh();
        $this->assertFalse($bank->is_active);

        $this->actingAs($this->admin)->patch(route('admin.question-banks.toggle', $bank));
        $bank->refresh();
        $this->assertTrue($bank->is_active);

$deleteResponse = $this->actingAs($this->admin)->delete(route('admin.question-banks.destroy', $bank));
        $deleteResponse->assertRedirect(route('admin.question-banks.index'));
        $this->assertDatabaseMissing('question_banks', ['id' => $bank->id]);
    }

public function test_admin_can_batch_upload_multiple_photos_and_manage_items(): void
    {
        Storage::fake('public');

        $bank = QuestionBank::create([
            'title' => 'Bank Soal Fisika Gelombang',
            'category' => 'Fisika',
            'description' => 'Soal diagram gelombang.',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

$photos = [
            UploadedFile::fake()->image('soal_gelombang_1.jpg', 600, 400),
            UploadedFile::fake()->image('soal_gelombang_2.png', 800, 600),
            UploadedFile::fake()->image('soal_gelombang_3.webp', 700, 500),
        ];

$uploadResponse = $this->actingAs($this->admin)->post(route('admin.question-banks.items.upload', $bank), [
            'images' => $photos,
        ]);

        $uploadResponse->assertSessionHas('success');
        $this->assertEquals(3, $bank->items()->count());

        $firstItem = $bank->items()->first();
        $this->assertNotNull($firstItem);
        Storage::disk('public')->assertExists($firstItem->image);
        $this->assertStringContainsString('/storage/', $firstItem->image_url);

$updateItemResponse = $this->actingAs($this->admin)->put(route('admin.question-banks.items.update', [$bank, $firstItem]), [
            'title' => 'Diagram Interferensi Gelombang Soal 1',
            'notes' => 'Gunakan rumus beda fase delta = 2 * pi * delta_x / lambda.',
            'order' => 1,
        ]);

        $updateItemResponse->assertSessionHas('success');
        $firstItem->refresh();
        $this->assertEquals('Diagram Interferensi Gelombang Soal 1', $firstItem->title);
        $this->assertStringContainsString('beda fase', $firstItem->notes);

$deleteItemResponse = $this->actingAs($this->admin)->delete(route('admin.question-banks.items.destroy', [$bank, $firstItem]));
        $deleteItemResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('question_bank_items', ['id' => $firstItem->id]);
        Storage::disk('public')->assertMissing($firstItem->image);
        $this->assertEquals(2, $bank->items()->count());
    }

public function test_students_can_browse_active_question_banks(): void
    {
        Storage::fake('public');

        $activeBank = QuestionBank::create([
            'title' => 'Bank Soal Kimia Organik',
            'category' => 'Kimia',
            'description' => 'Struktur molekul hidrokarbon.',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $item = QuestionBankItem::create([
            'question_bank_id' => $activeBank->id,
            'image' => 'question_banks/test_kimia.jpg',
            'title' => 'Struktur Benzena',
            'notes' => 'Senyawa aromatis dengan cincin karbon 6.',
            'order' => 1,
        ]);

$indexResponse = $this->actingAs($this->user)->get(route('user.question-banks.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Bank Soal Kimia Organik');
        $indexResponse->assertSee('Kimia');

$showResponse = $this->actingAs($this->user)->get(route('user.question-banks.show', $activeBank));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Bank Soal Kimia Organik');
        $showResponse->assertSee('Struktur Benzena');
        $showResponse->assertSee('Senyawa aromatis');
    }

public function test_students_cannot_access_draft_question_banks(): void
    {
        $draftBank = QuestionBank::create([
            'title' => 'Bank Soal Rahasia Belum Siap',
            'category' => 'Draft',
            'description' => 'Masih dalam pengerjaan admin.',
            'is_active' => false,
            'created_by' => $this->admin->id,
        ]);

$indexResponse = $this->actingAs($this->user)->get(route('user.question-banks.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Bank Soal Rahasia Belum Siap');

$showResponse = $this->actingAs($this->user)->get(route('user.question-banks.show', $draftBank));
        $showResponse->assertStatus(404);
    }
}
